Je vais parcourir l’arborescence, les routes, les couches métier, les DTO/requêtes/modèles et la configuration de tests afin de produire une documentation fidèle au projet, puis ajouter le document dans le dépôt.

# Guide d’architecture et de développement d’une API Laravel

> Document basé sur l’API `Tasks` actuelle du projet.  
> L’objectif est de conserver l’organisation existante tout en appliquant des conventions plus strictes de Clean Code, de typage et de séparation des responsabilités.

---

## 1. Objectif de l’API

L’application expose une API REST permettant de gérer des tâches :

- créer une tâche ;
- consulter une tâche ;
- lister les tâches ;
- filtrer les tâches ;
- modifier une tâche partiellement ;
- modifier uniquement son statut ;
- supprimer une tâche ;
- retourner des réponses JSON homogènes ;
- valider toutes les données entrantes.

L’API est versionnée sous :

```text
/api/v1
```

Cette version permet de faire évoluer l’API plus tard sans casser les clients utilisant une ancienne version.

---

# 2. Architecture générale

L’architecture recommandée est la suivante :

```text
Requête HTTP
    ↓
Route
    ↓
Form Request
    ↓
Controller
    ↓
Service
    ↓
Interface
    ↓
Repository
    ↓
Model Eloquent
    ↓
Base de données
```

Pour une réponse :

```text
Base de données
    ↓
Model Eloquent
    ↓
Repository
    ↓
Service
    ↓
Controller
    ↓
API Resource
    ↓
Réponse JSON
```

## Responsabilité de chaque couche

| Couche | Responsabilité | Ne doit pas faire |
|---|---|---|
| Route | Déclarer l’URL et le contrôleur appelé | Contenir de la logique métier |
| Form Request | Autoriser et valider les données entrantes | Modifier la base de données |
| Controller | Recevoir la requête et construire la réponse HTTP | Contenir les requêtes SQL ou la logique métier |
| Service | Orchestrer les règles métier et les cas d’utilisation | Gérer directement les détails HTTP |
| Interface | Définir le contrat du repository | Contenir l’implémentation SQL |
| Repository | Lire et modifier les modèles | Retourner des réponses HTTP |
| Model | Représenter une table et ses relations | Valider une requête HTTP |
| DTO | Transporter des données typées entre les couches | Remplacer une validation partielle |
| Resource | Transformer un modèle en JSON public | Effectuer une requête SQL |

---

# 3. Structure recommandée du projet

```text
app/
├── DTO/
│   ├── TaskDTO.php
│   ├── TaskFilterDTO.php
│   └── TaskStatusDTO.php
│
├── Http/
│   ├── Controllers/
│   │   └── TaskController.php
│   │
│   ├── Requests/
│   │   ├── StoreTaskRequest.php
│   │   ├── UpdateTaskRequest.php
│   │   ├── UpdateTaskStatusRequest.php
│   │   └── TaskFilterRequest.php
│   │
│   └── Resources/
│       └── TaskResource.php
│
├── Interfaces/
│   └── TaskInterface.php
│
├── Models/
│   └── Task.php
│
├── Repositories/
│   └── TaskRepository.php
│
├── Services/
│   └── TaskService.php
│
database/
├── factories/
│   └── TaskFactory.php
├── migrations/
│   └── xxxx_xx_xx_create_tasks_table.php
└── seeders/
    └── TaskSeeder.php

routes/
└── api.php

tests/
├── Feature/
│   └── TaskApiTest.php
└── Unit/
    └── TaskServiceTest.php
```

Le projet utilise actuellement `app/Repository`. La convention Laravel la plus courante est généralement `app/Repositories`, mais l’important est de choisir une convention et de la conserver partout.

---

# 4. Modèle de données

## Table `tasks`

La migration actuelle définit les colonnes suivantes :

| Colonne | Type | Null | Description |
|---|---|---:|---|
| `id` | entier auto-incrémenté | non | Identifiant de la tâche |
| `title` | chaîne, 50 caractères | non | Titre |
| `description` | chaîne, 255 caractères | oui | Description |
| `priority` | enum | non | `low`, `medium`, `high` |
| `status` | enum | non | `todo`, `in_progress`, `done` |
| `due_date` | date-heure | oui | Date d’échéance |
| `created_at` | date-heure | non | Date de création |
| `updated_at` | date-heure | non | Date de modification |

## Modèle Eloquent recommandé

```php
class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];
}
```

L’identifiant `id` ne devrait pas être placé dans `$fillable`. Il est généré par la base de données et ne doit pas être fourni par le client.

---

# 5. Routes de l’API

Les routes actuelles sont organisées sous `/api/v1` :

| Méthode | URL | Action | Description |
|---|---|---|---|
| `GET` | `/api/v1/tasks` | `index` | Lister et filtrer les tâches |
| `GET` | `/api/v1/tasks/{id}` | `show` | Consulter une tâche |
| `POST` | `/api/v1/tasks` | `store` | Créer une tâche |
| `PUT/PATCH` | `/api/v1/tasks/{id}` | `update` | Modifier une tâche |
| `PATCH` | `/api/v1/tasks/{id}/status` | `updateStatus` | Modifier uniquement le statut |
| `DELETE` | `/api/v1/tasks/{id}` | `destroy` | Supprimer une tâche |

Exemple :

```php
Route::prefix('v1')->group(function () {
    Route::get('tasks', [TaskController::class, 'index']);
    Route::get('tasks/{id}', [TaskController::class, 'show']);
    Route::post('tasks', [TaskController::class, 'store']);
    Route::match(['put', 'patch'], 'tasks/{id}', [TaskController::class, 'update']);
    Route::patch('tasks/{id}/status', [TaskController::class, 'updateStatus']);
    Route::delete('tasks/{id}', [TaskController::class, 'destroy']);
});
```

Il est recommandé d’utiliser `{task}` avec le binding Laravel, ou de convertir explicitement l’identifiant en entier :

```php
Route::get('tasks/{task}', [TaskController::class, 'show']);
```

---

# 6. Contrat des paramètres HTTP

## Paramètres de route

| Paramètre | Type | Obligatoire | Description |
|---|---|---:|---|
| `id` ou `task` | entier | oui | Identifiant de la tâche |

Exemple :

```text
GET /api/v1/tasks/15
```

## Paramètres de filtrage

Endpoint :

```text
GET /api/v1/tasks
```

| Paramètre | Type | Obligatoire | Valeurs |
|---|---|---:|---|
| `title` | chaîne | non | Recherche sur le titre |
| `status` | chaîne | non | `todo`, `in_progress`, `done` |
| `priority` | chaîne | non | `low`, `medium`, `high` |
| `page` | entier | non | Numéro de page |
| `per_page` | entier | non | Nombre de résultats par page |

Exemple :

```text
GET /api/v1/tasks?status=todo&priority=high&page=1&per_page=10
```

## Corps de création

Endpoint :

```text
POST /api/v1/tasks
```

| Champ | Type | Obligatoire | Contraintes |
|---|---|---:|---|
| `title` | chaîne | oui | Maximum 50 caractères |
| `description` | chaîne ou `null` | non | Maximum 255 caractères |
| `priority` | chaîne | oui | `low`, `medium`, `high` |
| `status` | chaîne | oui | `todo`, `in_progress`, `done` |
| `due_date` | chaîne ou `null` | non | Format recommandé : `Y-m-d H:i:s` |

Exemple :

```json
{
    "title": "Préparer la documentation",
    "description": "Décrire l'architecture de l'API",
    "priority": "high",
    "status": "todo",
    "due_date": "2026-10-15 18:00:00"
}
```

## Corps de modification

Endpoint :

```text
PATCH /api/v1/tasks/{id}
```

Tous les champs sont facultatifs :

```json
{
    "title": "Nouveau titre"
}
```

Seul `title` sera modifié. Les autres colonnes conserveront leur valeur actuelle.

Pour vider explicitement un champ nullable :

```json
{
    "description": null,
    "due_date": null
}
```

## Corps de modification du statut

Endpoint :

```text
PATCH /api/v1/tasks/{id}/status
```

```json
{
    "status": "in_progress"
}
```

Le champ `status` est obligatoire pour cet endpoint.

---

# 7. Validation avec les Form Requests

## Création

```php
class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'priority' => ['required', Rule::in([
                'low',
                'medium',
                'high',
            ])],
            'status' => ['required', Rule::in([
                'todo',
                'in_progress',
                'done',
            ])],
            'due_date' => [
                'nullable',
                'date_format:Y-m-d H:i:s',
            ],
        ];
    }
}
```

## Modification partielle

```php
class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:50'],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'priority' => [
                'sometimes',
                Rule::in(['low', 'medium', 'high']),
            ],
            'status' => [
                'sometimes',
                Rule::in(['todo', 'in_progress', 'done']),
            ],
            'due_date' => [
                'sometimes',
                'nullable',
                'date_format:Y-m-d H:i:s',
            ],
        ];
    }
}
```

La règle `sometimes` signifie :

- si le champ est absent, il n’est pas validé et ne sera pas modifié ;
- si le champ est présent, il doit respecter les règles ;
- si le champ est présent avec `null`, il faut ajouter `nullable`.

---

# 8. DTO et conversion des types

Un DTO sert à transporter des données structurées et typées. Il ne doit pas recevoir directement des données non converties provenant de la requête.

Une requête HTTP fournit généralement des chaînes :

```text
"2026-10-15 18:00:00"
```

Alors qu’un DTO peut exiger :

```php
?DateTime
```

Il faut donc convertir la valeur avant d’appeler le constructeur.

```php
use Carbon\Carbon;
use DateTime;

public static function fromRequest(StoreTaskRequest $request): self
{
    $dueDate = $request->validated('due_date');

    return new self(
        id: null,
        title: $request->validated('title'),
        description: $request->validated('description'),
        priority: $request->validated('priority'),
        status: $request->validated('status'),
        due_date: $dueDate !== null
            ? Carbon::createFromFormat('Y-m-d H:i:s', $dueDate)
            : null,
    );
}
```

La méthode `toArray()` doit retourner un tableau, car Eloquent attend un tableau pour `create()` et `update()` :

```php
public function toArray(): array
{
    return [
        'title' => $this->title,
        'description' => $this->description,
        'priority' => $this->priority,
        'status' => $this->status,
        'due_date' => $this->due_date?->format('Y-m-d H:i:s'),
    ];
}
```

À éviter :

```php
Task::create($taskDTO);
```

À utiliser :

```php
Task::create($taskDTO->toArray());
```

---

# 9. Gestion de la création

## Controller

Le contrôleur reçoit une `FormRequest`, crée un DTO, puis appelle le service.

```php
public function store(StoreTaskRequest $request): JsonResponse
{
    $task = $this->taskService->createTask(
        TaskDTO::fromRequest($request)
    );

    return response()->json([
        'message' => 'Task created successfully',
        'data' => new TaskResource($task),
    ], 201);
}
```

## Service

```php
public function createTask(TaskDTO $taskDTO): Task
{
    return $this->taskRepository->store($taskDTO);
}
```

## Repository

```php
public function store(TaskDTO $taskDTO): Task
{
    return Task::create($taskDTO->toArray());
}
```

Le contrôleur ne doit pas appeler directement `Task::create()`.

---

# 10. Gestion d’une modification partielle

Une mise à jour partielle ne doit pas utiliser un DTO complet avec des propriétés obligatoires comme :

```php
public string $priority;
public string $status;
```

Si `priority` n’est pas transmis, sa valeur sera `null`, ce qui provoquera une erreur de typage.

La bonne approche est de transmettre uniquement les données validées.

## Interface

```php
interface TaskInterface
{
    public function update(Task $task, array $attributes): Task;
}
```

## Repository

```php
public function update(Task $task, array $attributes): Task
{
    $task->update($attributes);

    return $task->fresh();
}
```

## Service

```php
public function updateTask(int $id, array $attributes): ?Task
{
    $task = $this->taskRepository->show($id);

    if ($task === null) {
        return null;
    }

    return $this->taskRepository->update($task, $attributes);
}
```

## Controller

```php
public function update(
    UpdateTaskRequest $request,
    int $id
): JsonResponse {
    $task = $this->taskService->updateTask(
        $id,
        $request->validated()
    );

    if ($task === null) {
        return response()->json([
            'message' => 'Task not found',
        ], 404);
    }

    return response()->json([
        'message' => 'Task updated successfully',
        'data' => new TaskResource($task),
    ]);
}
```

Cette méthode respecte la règle :

```text
Controller → Service → Repository → Model
```

---

# 11. Tableau des fonctions et des types

## `TaskController`

| Fonction | Paramètres | Retour | Statut HTTP |
|---|---|---|---:|
| `index` | `TaskFilterRequest $request` | `JsonResponse` | `200` |
| `store` | `StoreTaskRequest $request` | `JsonResponse` | `201` |
| `show` | `int $id` | `JsonResponse` | `200` ou `404` |
| `update` | `UpdateTaskRequest $request`, `int $id` | `JsonResponse` | `200` ou `404` |
| `updateStatus` | `UpdateStatusRequest $request`, `int $id` | `JsonResponse` | `200` ou `404` |
| `destroy` | `int $id` | `JsonResponse` | `200` ou `404` |

Le type de retour doit être déclaré :

```php
use Illuminate\Http\JsonResponse;
```

## `TaskService`

| Fonction | Paramètres | Retour |
|---|---|---|
| `getTaskFilter` | `TaskFilterDTO $filters` | `array` ou `LengthAwarePaginator` |
| `createTask` | `TaskDTO $taskDTO` | `Task` |
| `getById` | `int $id` | `?Task` |
| `updateTask` | `int $id`, `array $attributes` | `?Task` |
| `updateTaskStatus` | `int $id`, `array $attributes` | `?Task` |
| `deleteTask` | `int $id` | `bool` |

Une méthode utilisant `find()` doit accepter `null` :

```php
public function getById(int $id): ?Task
```

`find()` peut retourner :

```php
Task|null
```

## `TaskInterface` et repository

| Fonction | Paramètres | Retour |
|---|---|---|
| `getFilterTask` | `TaskFilterDTO $filters` | `Collection` ou `LengthAwarePaginator` |
| `show` | `int $id` | `?Task` |
| `store` | `TaskDTO $taskDTO` | `Task` |
| `update` | `Task $task`, `array $attributes` | `Task` |
| `updateStatus` | `Task $task`, `array $attributes` | `Task` |
| `destroy` | `Task $task` | `bool` |

Le type déclaré dans l’interface doit être identique au type déclaré dans le repository.

## `TaskDTO`

| Fonction | Paramètres | Retour |
|---|---|---|
| `__construct` | propriétés typées de la tâche | `void` |
| `fromRequest` | une Form Request adaptée | `self` |
| `toArray` | aucun | `array` |

## `TaskResource`

| Fonction | Paramètres | Retour |
|---|---|---|
| `toArray` | `Request $request` | `array<string, mixed>` |

---

# 12. Réponses JSON recommandées

## Création

HTTP `201` :

```json
{
    "message": "Task created successfully",
    "data": {
        "id": 1,
        "title": "Préparer la documentation",
        "description": null,
        "priority": "high",
        "status": "todo",
        "due_date": null,
        "created_at": "2026-10-11T15:00:00.000000Z",
        "updated_at": "2026-10-11T15:00:00.000000Z"
    }
}
```

## Consultation

HTTP `200` :

```json
{
    "data": {
        "id": 1,
        "title": "Préparer la documentation",
        "priority": "high",
        "status": "todo"
    }
}
```

## Ressource inexistante

HTTP `404` :

```json
{
    "message": "Task not found"
}
```

## Erreur de validation

HTTP `422` :

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "priority": [
            "The selected priority is invalid."
        ]
    }
}
```

Toutes les erreurs d’une API doivent rester au format JSON, y compris les erreurs `404`.

---

# 13. Pagination et filtrage

Le repository doit idéalement retourner un `LengthAwarePaginator` lorsque la pagination est demandée.

Exemple :

```php
public function getFilterTask(
    TaskFilterDTO $filters
): Collection|LengthAwarePaginator {
    $query = Task::query()
        ->when(
            $filters->status !== null,
            fn ($query) => $query->where('status', $filters->status)
        )
        ->when(
            $filters->priority !== null,
            fn ($query) => $query->where('priority', $filters->priority)
        )
        ->when(
            $filters->title !== null,
            fn ($query) => $query->where(
                'title',
                'like',
                '%' . $filters->title . '%'
            )
        );

    if ($filters->page !== null) {
        return $query->paginate($filters->per_page);
    }

    return $query->get();
}
```

Il faut éviter :

```php
$query->paginate($perPage)->items();
```

Cette écriture supprime les métadonnées de pagination comme :

- `current_page` ;
- `last_page` ;
- `per_page` ;
- `total` ;
- `links`.

Si l’API annonce une pagination, elle doit retourner l’objet paginé complet.

---

# 14. Erreurs rencontrées et prévention

## 14.1 Passer un DTO à `create()`

Erreur :

```text
Argument #1 must be of type array, TaskDTO given
```

Cause :

```php
Task::create($taskDTO);
```

Correction :

```php
Task::create($taskDTO->toArray());
```

---

## 14.2 Déclarer un `int` mais envoyer `null`

Erreur :

```text
Argument #1 ($id) must be of type int, null given
```

Cause :

```php
public int $id;
```

avec :

```php
id: null
```

Correction :

```php
public ?int $id;
```

Cependant, pour une création, il est préférable de ne pas mettre `id` dans le DTO de création. L’identifiant est généré par la base de données.

---

## 14.3 Déclarer un `DateTime` mais recevoir une chaîne

Erreur :

```text
Argument must be of type ?DateTime, string given
```

Cause :

```php
due_date: $request->validated('due_date')
```

Correction :

```php
due_date: $dueDate !== null
    ? Carbon::createFromFormat('Y-m-d H:i:s', $dueDate)
    : null
```

Il faut également valider le format :

```php
'due_date' => ['nullable', 'date_format:Y-m-d H:i:s'],
```

---

## 14.4 Utiliser un DTO complet pour une modification partielle

Erreur :

```text
Argument #4 ($priority) must be of type string, null given
```

Cause : un champ obligatoire dans le DTO n’est pas fourni dans un `PATCH`.

Pour une modification partielle :

```php
$request->validated()
```

doit être transmis comme tableau au service et au repository.

La validation doit utiliser :

```php
'sometimes'
```

---

## 14.5 Appeler une propriété comme une méthode

Incorrect :

```php
$this->title()
$this->priority()
$this->id()
```

Correct :

```php
$this->title
$this->priority
$this->id
```

---

## 14.6 Mauvais nom de colonne dans les casts

La colonne est :

```text
due_date
```

Le cast doit donc être :

```php
protected $casts = [
    'due_date' => 'datetime',
];
```

et non :

```php
'date_due'
```

---

## 14.7 Déclarer `Task` alors que `find()` peut retourner `null`

Incorrect :

```php
public function show(int $id): Task
{
    return Task::find($id);
}
```

Correct :

```php
public function show(int $id): ?Task
{
    return Task::find($id);
}
```

---

## 14.8 Mélanger les types de requêtes dans le repository

Le repository ne devrait pas recevoir directement :

```php
UpdatetaskRequest
UpdateStatusRequest
```

Il devrait recevoir des données métier déjà validées :

```php
array $attributes
```

Cela rend le repository indépendant de HTTP et plus facilement testable.

---

## 14.9 Utiliser `toArray()` au lieu de `validated()`

Pour modifier une ressource :

```php
$request->validated()
```

est préférable à :

```php
$request->toArray()
```

`validated()` garantit que seules les données autorisées par les règles de validation sont transmises.

---

# 15. Conventions de nommage

Utiliser des noms cohérents et conformes aux conventions Laravel :

| Élément | Convention recommandée |
|---|---|
| Modèle | `Task` |
| Table | `tasks` |
| Contrôleur | `TaskController` |
| Request de création | `StoreTaskRequest` |
| Request de modification | `UpdateTaskRequest` |
| Repository | `TaskRepository` |
| Interface | `TaskInterface` |
| Resource | `TaskResource` |
| Méthode de création | `store` ou `create` |
| Méthode de modification | `update` |
| Méthode de suppression | `destroy` |

Éviter les noms comme :

```text
StoretaskRequest
UpdatetaskRequest
```

Même si PHP les accepte, ils sont moins lisibles et ne respectent pas la convention PascalCase.

---

# 16. Injection de dépendance

Le contrôleur dépend du service :

```php
public function __construct(
    private readonly TaskService $taskService
) {
}
```

Le service dépend de l’interface :

```php
public function __construct(
    private readonly TaskInterface $taskRepository
) {
}
```

Le provider associe l’interface à son implémentation :

```php
public function register(): void
{
    $this->app->bind(
        TaskInterface::class,
        TaskRepository::class
    );
}
```

Cette organisation permet de remplacer facilement le repository par :

- un faux repository pour les tests ;
- un repository utilisant une autre source de données ;
- une implémentation spécialisée.

---

# 17. Tests à prévoir

Les tests Feature doivent couvrir le comportement HTTP :

| Scénario | Résultat attendu |
|---|---|
| Créer une tâche valide | HTTP `201` |
| Refuser une tâche sans titre | HTTP `422` |
| Refuser une priorité inconnue | HTTP `422` |
| Lister les tâches | HTTP `200` |
| Filtrer par statut | Résultats filtrés |
| Filtrer par priorité | Résultats filtrés |
| Rechercher par titre | Résultats correspondants |
| Paginer les tâches | Métadonnées de pagination présentes |
| Consulter une tâche existante | HTTP `200` |
| Consulter une tâche inexistante | HTTP `404` |
| Modifier un seul champ | Les autres champs restent inchangés |
| Modifier le statut | HTTP `200` |
| Supprimer une tâche | HTTP `200` ou `204` |
| Vérifier une tâche supprimée | HTTP `404` |

Exemple :

```php
$response = $this->postJson('/api/v1/tasks', [
    'title' => 'Nouvelle tâche',
    'priority' => 'high',
    'status' => 'todo',
]);

$response
    ->assertCreated()
    ->assertJsonPath('data.title', 'Nouvelle tâche');
```

Les tests doivent utiliser une base SQLite en mémoire afin d’être rapides et isolés.

---

# 18. Checklist avant livraison

## Architecture

- [ ] Le contrôleur ne contient pas de requête Eloquent.
- [ ] Le contrôleur appelle le service.
- [ ] Le service appelle l’interface du repository.
- [ ] Le repository est le seul responsable des opérations Eloquent.
- [ ] Les interfaces et implémentations ont les mêmes signatures.
- [ ] Les DTO ne reçoivent pas de types non convertis.

## Validation

- [ ] Chaque endpoint possède une Form Request adaptée.
- [ ] Les règles de création et de modification sont séparées.
- [ ] Les modifications partielles utilisent `sometimes`.
- [ ] Les champs nullable utilisent `nullable`.
- [ ] Les dates utilisent un format explicite.
- [ ] Les valeurs enum sont limitées avec `Rule::in()`.

## Types

- [ ] `find()` retourne `?Task`.
- [ ] Les méthodes ont un type de retour explicite.
- [ ] `Task::create()` reçoit un tableau.
- [ ] `update()` reçoit un tableau d’attributs.
- [ ] Les propriétés ne sont pas appelées comme des méthodes.
- [ ] Les types `string`, `int`, `DateTime` et `null` sont cohérents.

## API

- [ ] Les routes sont versionnées avec `/api/v1`.
- [ ] Les réponses sont toujours en JSON.
- [ ] Les statuts HTTP sont appropriés.
- [ ] Les erreurs `404` et `422` sont documentées.
- [ ] Les `PUT` et `PATCH` ont un comportement clairement défini.
- [ ] La pagination conserve ses métadonnées.
- [ ] Les exemples Postman utilisent des valeurs acceptées par la validation.

## Documentation et maintenance

- [ ] Le README correspond au code réellement exécuté.
- [ ] La collection Postman correspond aux routes et aux enums actuels.
- [ ] Les secrets ne sont pas versionnés.
- [ ] Les migrations sont reproductibles.
- [ ] Les tests couvrent les parcours critiques.
- [ ] Les noms de fichiers respectent les conventions Laravel.

---

# 19. Flux recommandé complet

Pour créer une tâche :

```text
POST /api/v1/tasks
    ↓
StoreTaskRequest valide le JSON
    ↓
TaskController reçoit la requête
    ↓
TaskDTO convertit les valeurs typées
    ↓
TaskService orchestre la création
    ↓
TaskRepository convertit le DTO en tableau
    ↓
Task::create(...)
    ↓
TaskResource transforme le modèle
    ↓
Réponse JSON HTTP 201
```

Pour modifier uniquement le titre :

```text
PATCH /api/v1/tasks/1
{
    "title": "Nouveau titre"
}
    ↓
UpdateTaskRequest valide uniquement title
    ↓
Controller récupère validated()
    ↓
Service recherche la tâche
    ↓
Repository exécute update(['title' => ...])
    ↓
Les autres colonnes restent inchangées
    ↓
TaskResource retourne la tâche mise à jour
```

Cette organisation permet de conserver une architecture lisible, testable et évolutive tout en évitant les erreurs de typage et de responsabilité rencontrées dans l’implémentation actuelle.
