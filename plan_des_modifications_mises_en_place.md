## Plan pour la réalisation du projet Laravel REST API — Task Management API :
Pour la réalisations de cet api, nous allons mettre en place les modifications suivantes:

1- Dans un premier temps, nous allons adpter la structure du projet pour qu'elle soit destinée à acceuillir un api.

```php
//commande pour adapter la structure du projet à celle d'un api
php artisan install:api
```
2 - Nous allons passer à la mise en place du model pour la base de données:

Nous allons donc créer une table nommée "tasks" qui contiendra les colonnes suivantes:
- id (auto-increment)
- title (string)                         - obligatoire
- description (text)                     - optionnelle
- priority (enum: low, medium, high)     - obligatoire   par défaut medium
- status (enum: todo, in_progress, done) - obligatoire   par défaut todo
-due_date (date)                         - optionnelle
- timestamps (création et modification)  - automatique


```php
// Commande pour créer la migration de la table "tasks"
php artisan make:migration create_tasks_table
```
Suite à la création de la migration, nous allons modifier le fichier de migration pour ajouter les colonnes nécessaires à la table "tasks".
le fichier de migration final ressemblera à ceci:

```php
$table->id();
$table->string('title');
$table->text('description')->nullable();
$table->enum('priority', ['low', 'medium', 'high']);
$table->enum('status', ['todo', 'in_progress', 'done']);
$table->date('due_date')->nullable();
$table->timestamps();
```


3 - Nous allons ensuite créer le model correspondant à la table "tasks" pour interagir avec la base de données.

```php
// Commande pour créer le model "Task"
php artisan make:model Task
```
par la suite nous modifierons le modèle pour ajouter les propriétés nécessaires à la table "tasks".
le fichier de modèle final ressemblera à ceci:

```php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'due_date'
    ];
}
```

``` php
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Model :: shouldBeStrict(! app()->isProduction());
    }
}
```
4- Nous allons ensuite créer le contrôleur pour gérer les requêtes HTTP pour l'API.

Avant de créer le controlleur, nous allons mettre en place les forms requests pour la validation des données entrantes pour les méthodes store 
et update du contrôleur TaskController.
Le fichier de validation pour la méthode store ressemblera à ceci:
```php
class StoreTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:todo,in_progress,done',
            'due_date' => 'nullable|date',
        ];
    }
}
```
Par la suite, nous passerons à la mise en place du fichier de validation pour la méthode update qui ressemblera à ceci:
```php
class UpdateTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|required|in:low,medium,high',
            'status' => 'sometimes|required|in:todo,in_progress,done',
            'due_date' => 'nullable|date',
        ];
    }
}
```

```php
// Commande pour créer le contrôleur "TaskController"
php artisan make:controller TaskController --api
// l'option --api permet de créer un contrôleur avec les méthodes index, store, show, update et destroy pour gérer les requêtes HTTP pour l'API.
```

5- Nous allons ensuite créer les routes pour l'API dans le fichier routes/api.php.
Voici le tableau des routes pour l'API:
| Méthode HTTP | Endpoint                     | Description                  |
|--------------|------------------------------|------------------------------|
| GET          | `/api/v1/tasks`             | Liste + filtres + pagination |
| GET          | `/api/v1/tasks/{id}`        | Détail                       |
| POST         | `/api/v1/tasks`             | Création                     |
| PUT/PATCH    | `/api/v1/tasks/{id}`        | Modification                 |
| PATCH        | `/api/v1/tasks/{id}/status` | Transition de statut         |
| DELETE       | `/api/v1/tasks/{id}`        | Suppression                  |

```php
// pour regrouper toutes les routes de l'api, nous allons utiliser la méthode apiResource qui permet de créer automatiquement les routes pour les méthodes index, store, show, update et destroy du contrôleur TaskController.
route::apiResource('tasks', TaskController::class);
// ou pour plus de personnalisation
route::prefix('v1')->group(function () {
    ...
});
```

6- Après ces étapes, nous allons mettre en place le fichier Ressource de l'API pour transformer les données de la base de données en JSON.

Nous allons créer un fichier de ressource pour la table "tasks" qui permettra de transformer les données de la base de données en JSON.
```php
// Commande pour créer le fichier de ressource "TaskResource"
php artisan make:resource TaskResource
```
Le fichier de ressource final ressemblera à ceci:
```php
class TaskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'due_date' => $this->due_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```



#### Fonctionnalités obligatoires
Voici la liste des fonctionnalités obligatoires pour l'API:
- CRUD complet.
- Changement de statut.
- Filtre par statut et priorité.
- Recherche simple sur le titre.
- Pagination de la liste.
- Validation backend.
- Gestion d’une ressource inexistante.
- Réponses JSON cohérentes.

#### Contraintes sur le projet:
les contraintes sur le projet sont les suivantes:
- utilisation de Laravel + MySQL.
- Routes sous /api/v1.
- Migration reproductible.
- Validation structurée.
- API Resource ou représentation JSON propre.
- Au moins quelques tests Feature sur les parcours critiques.
- Historique Git progressif.
- Aucun secret versionné. 

#### voici la list des différentes scénarios de tests à réaliser pour l'API:
1. Créer une tâche valide.
2. Refuser une création invalide.
3. Lister avec pagination.
4. Filtrer par statut.
5. Rechercher par titre.
6. Modifier une tâche.
7. Changer son statut.
8. Demander une tâche inexistante.
9. Supprimer une tâche puis vérifier son absence. 


#### les Livrables
- Repository Git.
- Projet Laravel exécutable.
- README d’installation et contrat API.
- Tests automatisés et/ou collection API complémentaire.
- Note courte : choix techniques, difficultés et améliorations possibles. 
