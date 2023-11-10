# Todo List API

Test task for creating a to-do list API with Laravel.
You can see the task description [here](task.md)

## Table of contents

- [Features](#features)
- [Deployment](#deployment)
- [Logic](#logic)
- [OpenAPI](#openapi)

## Features

- Performs basic CRUD operations
- Full-text search filter
- Filtering by multiple filters
- Sorting by multiple fields
- Infinite subtasks nesting
- Switching between tree view and plain view
- OpenAPI documentation with Swagger UI

## Deployment

- Clone this repository
```bash
git clone git@github.com:nestermaks/test-todo-api-laravel.git
```

- cd into the project
```bash
cd test-todo-api-laravel
```

- copy env example file as env
```bash
cp .env.example .env
```

- start the server
```bash
docker-compose up -d
```

- install project dependencies
```bash
docker-compose exec app composer install
```

- run the migrations
```bash
docker-compose exec app php artisan migrate
```

- run seeders
```bash
docker-compose exec app php artisan db:seed
```

To use an API you have to be authenticated with bearer token. There are 2 dummy users with tokens:
- johndoe@example.com - ```1|R5VXkyFjG3VLvkbQTGtACXCpERXT4unnQphxRR2P35b3048d```
- janedoe@example.com - ```2|2RLeikge7K9572I5XrmuJMcUeTlzKaWU6SdJB6ukf167b665```

Also these tokens present in the .env file.
You can use these tokens e.g. in Postman or in Swagger UI.

## Logic

### API Functionality

- Retrieve a list of tasks according to the filter
- Create a new task
- Edit an existing task
- Mark a task as completed
- Delete a task

### List Retrieval Options

- Filter by the status field
- Filter by the priority field
- Filter by the title and description fields (full-text search)
- Sort by createdAt, completedAt, priority. Support sorting by two fields, for example, priority desc, createdAt asc.

### User Restrictions

- Cannot modify or delete tasks belonging to others
- Cannot delete a completed task
- Cannot delete a task with completed subtasks
- Cannot mark a task as completed if it has uncompleted subtasks

## OpenAPI

You can easily test an api and take a look at full documentation with Swagger UI.
After you deployed this project, just visit endpoint ```/api/documentation```. For example: [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation).
Click "Authorize" button and use one of the user tokens provided above.
