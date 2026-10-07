# Tasks API

API REST Laravel pour créer et gérer des tâches.

## Prérequis

- PHP 8.3 ou supérieur ;
- Composer ;
- SQLite (utilisé par défaut) ;
- Node.js et npm uniquement si les assets front-end doivent être compilés.

## Installation

Depuis la racine du projet :

```bash
git clone <url-du-depot>
cd test_laravel_api-innovertec
composer install
```

Créer le fichier d'environnement et générer la clé Laravel :

```bash
copy .env.example .env    # Windows
# cp .env.example .env    # macOS/Linux
php artisan key:generate
```

La configuration fournie utilise SQLite :

```dotenv
DB_CONNECTION=mysql
```


Initialiser le schéma :

```bash
php artisan migrate
```

Pour installer les données de démonstration (un utilisateur et des tâches) :

```bash
php artisan migrate:fresh --seed
```

## Démarrage

Lancer le serveur de développement :

```bash
php artisan serve
```

L'API est alors disponible à l'adresse `http://127.0.0.1:8000`.

Vérifier les routes exposées :

```bash
php artisan route:list --path=api
```

## Tests

```bash
php artisan test
```

## Contrat API

### Convention générale

- Base URL locale : `http://127.0.0.1:8000/api`
- Version courante : `/v1`
- Les requêtes JSON doivent envoyer `Accept: application/json`.
- Pour `POST`, `PUT` et `PATCH`, envoyer également `Content-Type: application/json`.
- Les endpoints de tâches sont publics dans la configuration actuelle.
- `GET /api/user` nécessite une authentification Laravel Sanctum (`Authorization:
  Bearer <token>`).

### Ressource `Task`

| Champ | Type | Obligatoire | Valeurs / contraintes |
| --- | --- | --- | --- |
| `id` | entier | réponse uniquement | Identifiant auto-incrémenté |
| `title` | chaîne | oui à la création et mise à jour | 50 caractères maximum |
| `description` | chaîne ou `null` | non | 250 caractères maximum |
| `priority` | chaîne | oui à la création et mise à jour | `low`, `medium`, `high` |
| `status` | chaîne | oui à la création et mise à jour | `todo`, `in_progress`, `done` |
| `due_date` | date/chaîne ou `null` | non | Date d'échéance facultative |
| `created_at` | date ISO 8601 | réponse uniquement | Date de création |
| `updated_at` | date ISO 8601 | réponse uniquement | Date de dernière modification |

Les erreurs de validation renvoient HTTP `422` avec le format d'erreur Laravel
(`message` et `errors`).

### Lister les tâches

```http
GET /api/v1/tasks
```

Filtres et paramètres optionnels :

| Paramètre | Description |
| --- | --- |
| `status` | Filtre sur `todo`, `in_progress` ou `done` |
| `priority` | Filtre sur `low`, `medium` ou `high` |
| `per_page` | Nombre d'éléments par page, 10 par défaut |
| `page` | Active la réponse paginée Laravel |

Sans paramètre `page`, la réponse est HTTP `200` :

```json
{
  "page": [
    {
      "id": 1,
      "title": "Préparer la documentation",
      "description": "Décrire l'installation et les endpoints",
      "priority": "high",
      "status": "todo",
      "due_date": null,
      "created_at": "2026-10-06T10:00:00.000000Z",
      "updated_at": "2026-10-06T10:00:00.000000Z"
    }
  ]
}
```

Avec `page`, la réponse est la structure de pagination Laravel et contient
notamment `current_page`, `data`, `per_page`, `total`, `last_page` et les liens de
navigation.

Exemple :

```bash
curl "http://127.0.0.1:8000/api/v1/tasks?status=todo&priority=high&page=1&per_page=10" \
  -H "Accept: application/json"
```

### Consulter une tâche

```http
GET /api/v1/tasks/{task}
```

Réponse HTTP `200` :

```json
{
  "data": {
    "id": 1,
    "title": "Préparer la documentation",
    "description": "Décrire l'installation et les endpoints",
    "priority": "high",
    "status": "todo",
    "due_date": null,
    "created_at": "2026-10-06T10:00:00.000000Z",
    "updated_at": "2026-10-06T10:00:00.000000Z"
  }
}
```

Une tâche inexistante renvoie HTTP `404`.

### Créer une tâche

```http
POST /api/v1/tasks
```

Corps minimal valide :

```json
{
  "title": "Préparer la documentation",
  "description": "Décrire l'installation et les endpoints",
  "priority": "high",
  "status": "todo",
  "due_date": null
}
```

Réponse HTTP `201` :

```json
{
  "message": "create with success",
  "data": {
    "id": 1,
    "title": "Préparer la documentation",
    "description": "Décrire l'installation et les endpoints",
    "priority": "high",
    "status": "todo",
    "due_date": null,
    "created_at": "2026-10-06T10:00:00.000000Z",
    "updated_at": "2026-10-06T10:00:00.000000Z"
  }
}
```

Exemple :

```bash
curl -X POST "http://127.0.0.1:8000/api/v1/tasks" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"title\":\"Préparer la documentation\",\"priority\":\"high\",\"status\":\"todo\"}"
```

### Modifier une tâche

```http
PUT|PATCH /api/v1/tasks/{task}
```

La requête utilise les mêmes champs et contraintes que la création. Les champs
`title`, `priority` et `status` restent obligatoires, même avec `PATCH`.

Réponse HTTP `200` :

```json
{
  "message": "Task update with success",
  "data": { "...": "tâche mise à jour" }
}
```

### Modifier uniquement le statut

```http
PATCH /api/v1/tasks/{task}/status
```

Corps :

```json
{
  "status": "in_progress"
}
```

Réponse HTTP `200` :

```json
{
  "message": "Task status update witch success",
  "data": {
    "id": 1,
    "status": "in_progress",
    "updated at": "2026-10-06T10:15:00.000000Z"
  }
}
```

### Supprimer une tâche

```http
DELETE /api/v1/tasks/{task}
```

Réponse HTTP `200` :

```json
{
  "message": "delete successfully"
}
```

## Authentification Sanctum

La route suivante est protégée :

```http
GET /api/user
Authorization: Bearer <token>
```

Elle renvoie l'utilisateur authentifié. Les routes de création, lecture,
modification et suppression des tâches ne demandent pas de token dans l'état
actuel du projet.

## Collection Postman

Le fichier [`test_api_postman_collection.json`](test_api_postman_collection.json)
contient les requêtes principales. Importez-le dans Postman puis définissez
`base_url` à `http://127.0.0.1:8000`.

## Licence

Ce projet est basé sur Laravel et est distribué sous licence MIT.
