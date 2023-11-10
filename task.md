# Todo List API

## Overview

To implement an API that allows managing a task list.

### API Functionality

- Retrieve a list of tasks according to the filter
- Create a new task
- Edit an existing task
- Mark a task as completed
- Delete a task

### List Retrieval Options

- Filter by the status field
- Filter by the priority field
- Filter by the title and description fields (full-text search should be implemented)
- Sort by createdAt, completedAt, priority. Support sorting by two fields, for example, priority desc, createdAt asc.

### User Restrictions

- Cannot modify or delete tasks belonging to others
- Cannot delete a completed task
- Cannot mark a task as completed if it has uncompleted subtasks

### Task Properties

- status (todo, done)
- priority (1...5)
- title
- description
- createdAt
- completedAt

### Subtasks

- Tasks may have unlimited nesting levels for subtasks.

## Technical Requirements

- **Minimum Version:** PHP 8.1
- **Framework:** Laravel / Symfony
- Code should be uploaded to a public repository.

## Recommendations

### Documentation

- Accompany the test with a well-written README.md
- Include Open API documentation for the test
- Wrap the project in Docker Compose
- Use English language for documentation and code comments

### Architecture

- Utilize built-in framework functionality as much as possible
- Use a service layer for business logic
- Use repositories to retrieve data from the database
- Use DTOs
- Use Enums
- Employ strict typing
- Follow RESTful routing
- Use recursion or references for forming the task tree

### Code Style

- Follow PSR-12
- Avoid working with arrays

### Database

- Use seeders/fixtures for database population
- Utilize indexes
