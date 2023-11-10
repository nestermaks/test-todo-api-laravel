<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Task API",
 *     version ="1.0",
 * ),
 *
 * @OA\PathItem(path="/api/v1/"),
 *
 * @OA\Components(
 *     @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer"
 *     )
 * ),
 *
 * @OA\Get(
 *   path="/api/v1/tasks",
 *   summary="Lists all user's tasks",
 *   tags={ "Tasks" },
 *   security={{ "bearerAuth": {} }},
 *   description="# Shows a list of authenticated user's tasks.
You may switch between tree and plain mode, use filters and sorters.

## Tree mode
In tree mode filters work only with a top level. It shows the filtered tasks with unfiltered subtasks.
Sorting works with a top level at first, then it goes one level down to subtasks etc.

## Filters
You can filter by priority, status or use a search string. You can use different filters at once.

## Sorters
You can sort by priority, creation date and completion date. You can use different sorters at once.",
 *   @OA\Parameter(
 *       name="keep-tree",
 *       in="query",
 *       description="Show tasks with nested subtasks, when switched to '1'. One level view when '0'",
 *       required=false,
 *
 *       @OA\Schema(type="bool", enum={0, 1}),
 *   ),
 *
 *   @OA\Parameter(
 *       name="filters[priority]",
 *       in="query",
 *       description="A comma separated list of priority levels. From 1 to 5",
 *       required=false,
 *
 *       @OA\Schema(type="string"),
 *       style="form",
 *       explode=true
 *   ),
 *
 *   @OA\Parameter(
 *       name="filters[status]",
 *       in="query",
 *       description="Task status 'done' or 'todo'.",
 *       required=false,
 *
 *       @OA\Schema(type="string", enum={"done", "todo"}),
 *       style="form",
 *       explode=false
 *   ),
 *
 *   @OA\Parameter(
 *       name="filters[search]",
 *       in="query",
 *       description="Search in titles and descriptions. Search works with then 3+ characters strings",
 *       required=false,
 *       @OA\Schema(type="string"),
 *       style="form",
 *       explode=false
 *   ),
 *
 *   @OA\Parameter(
 *       name="sorters[priority]",
 *       in="query",
 *       description="Sorts by priority.",
 *       required=false,
 *
 *       @OA\Schema(type="string", enum={"asc", "desc"}),
 *       style="form",
 *       explode=false
 *   ),
 *
 *   @OA\Parameter(
 *       name="sorters[createdAt]",
 *       in="query",
 *       description="Sorts by time of task creation.",
 *       required=false,
 *
 *       @OA\Schema(type="string", enum={"asc", "desc"}),
 *       style="form",
 *       explode=false
 *   ),
 *
 *   @OA\Parameter(
 *       name="sorters[completedAt]",
 *       in="query",
 *       description="Sorts by time, when task was marked as completed.",
 *       required=false,
 *
 *       @OA\Schema(type="string", enum={"asc", "desc"}),
 *       style="form",
 *       explode=false
 *   ),
 *
 *   @OA\Response(
 *       response=200,
 *       description="OK",
 *
 *       @OA\JsonContent(
 *           @OA\Property(property="data", type="array", @OA\Items(
 *               @OA\Property(property="id", type="integer", example=22),
 *               @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
 *               @OA\Property(property="status", type="string", example="todo"),
 *               @OA\Property(property="priority", type="integer", example=2),
 *               @OA\Property(property="title", type="string", example="Clean the house"),
 *               @OA\Property(property="description", type="string", example="Clean everything in that house."),
 *               @OA\Property(
 *                   property="completedAt",
 *                   type="string",
 *                   format="date-time",
 *                   nullable=true,
 *                   example=null,
 *               ),
 *               @OA\Property(
 *                   property="createdAt",
 *                   type="string",
 *                   format="date-time",
 *                   example="29-10-2022 20:44:30",
 *               ),
 *               @OA\Property(property="subtasks", type="array", @OA\Items(
 *                   @OA\Property(property="id", type="integer", example=23),
 *                   @OA\Property(property="parent_id", type="integer", example=22),
 *                   @OA\Property(property="status", type="string", example="done"),
 *                   @OA\Property(property="priority", type="integer", example=5),
 *                   @OA\Property(property="title", type="string", example="Wash the windows"),
 *                   @OA\Property(
 *                       property="description",
 *                       type="string",
 *                       example="They have to shine bright like a diamonds"
 *                   ),
 *                   @OA\Property(
 *                       property="completedAt",
 *                       type="string",
 *                       format="date-time",
 *                       nullable=true,
 *                       example="31-12-2022 23:44:59",
 *                   ),
 *                   @OA\Property(
 *                       property="createdAt",
 *                       type="string",
 *                       format="date-time",
 *                       example="23-11-2022 20:44:30",
 *                   ),
 *                  @OA\Property(property="subtasks", type="array", @OA\Items(type="object"), example={}),
 *                )),
 *           )),
 *       )
 *   ),
 *     @OA\Response(
 *         response=401,
 *         description="If user is not logged in",
 *         @OA\JsonContent(
*              @OA\Property(
*                  property="message",
*                  type="object",
*                  example="Unauthenticated"
*              ),
 *         )
 *     ),
 * ),
 *
 * @OA\Post(
 *     path="/api/v1/tasks",
 *     summary="Adds a new task (or subtask)",
 *     tags={ "Tasks" },
 *     security={{ "bearerAuth": {} }},
 *     description="# Creates task/subtask for authenticated user
### Title and description
To create a new task you must specify title and description.

### Parent id
Optionally you can specify id of a task you wish to inherit in a field 'parent_id' to create a subtask.<br>
You cannot specify parent_id of a task, that doesn't belong to you.

### Priority
If priority is not specified, it will be created with default value of 1.",
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="parent_id", type="integer", example=null, nullable=true),
 *                 @OA\Property(property="priority", type="integer", nullable=true, minimum=1, maximum=5, example=4),
 *                 @OA\Property(property="title", type="string", maximum=255, example="Clean the house"),
 *                 @OA\Property(property="description", type="string", maximum=3000, example="Clean everything in that house."),
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="OK",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items(
 *               @OA\Property(property="id", type="integer", example=22),
 *               @OA\Property(property="userId", type="integer", example=null),
 *               @OA\Property(property="parentId", type="integer", example=null),
 *               @OA\Property(property="status", type="string", example="todo"),
 *               @OA\Property(property="priority", type="integer", example=4),
 *               @OA\Property(property="title", type="string", example="Clean the house"),
 *               @OA\Property(property="description", type="string", example="Clean everything in that house."),
 *               @OA\Property(
 *                   property="completedAt",
 *                   type="string",
 *                   format="date-time",
 *                   nullable=true,
 *                   example=null,
 *               ),
 *               @OA\Property(
 *                   property="createdAt",
 *                   type="string",
 *                   format="date-time",
 *                   example="29-10-2022 20:44:30",
 *               ),
 *             )),
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="If user is not logged in",
 *         @OA\JsonContent(
*              @OA\Property(
*                  property="message",
*                  type="object",
*                  example="Unauthenticated"
*              ),
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden operations",
 *         @OA\JsonContent(
*              @OA\Property(
*                  property="message",
*                  type="object",
*                  example="You cannot add subtask to the task of another user"
*              ),
 *         )
 *     ),
 * ),
 * @OA\Patch(
 *     path="/api/v1/tasks/{id}",
 *     summary="Updates a task",
 *     tags={ "Tasks" },
 *     security={{ "bearerAuth": {} }},
 *     description="# Updates a task/subtask for authenticated user
### Id
You must specify an id of a task you want to update in the URL.

### Json body
You can specify such fields as: parent_id, priority, title, description.<br>
All fields are optional.

### Parent id
You can specify id of a task you wish to inherit in a field 'parent_id' to attach this task to another one.<br>
You cannot specify parent_id of a task, that doesn't belong to you,<br>
neither you can specify a descendant task id as a parent id",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Specify an id of a task you wish to update",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="parent_id", type="integer", example=null, nullable=true),
 *                 @OA\Property(property="priority", type="integer", nullable=true, minimum=1, maximum=5, example=4),
 *                 @OA\Property(property="title", type="string", nullable=true, maximum=255, example="Clean the house"),
 *                 @OA\Property(property="description", type="string", nullable=true, maximum=3000, example="Clean everything in that house."),
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items(
 *               @OA\Property(property="id", type="integer", example=22),
 *               @OA\Property(property="userId", type="integer", example=null),
 *               @OA\Property(property="parentId", type="integer", example=null),
 *               @OA\Property(property="status", type="string", example="todo"),
 *               @OA\Property(property="priority", type="integer", example=4),
 *               @OA\Property(property="title", type="string", example="Clean the house"),
 *               @OA\Property(property="description", type="string", example="Clean everything in that house."),
 *               @OA\Property(
 *                   property="completedAt",
 *                   type="string",
 *                   format="date-time",
 *                   nullable=true,
 *                   example=null,
 *               ),
 *               @OA\Property(
 *                   property="createdAt",
 *                   type="string",
 *                   format="date-time",
 *                   example="29-10-2022 20:44:30",
 *               ),
 *             )),
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="If user is not logged in",
 *         @OA\JsonContent(
*              @OA\Property(
*                  property="message",
*                  type="object",
*                  example="Unauthenticated"
*              ),
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden operations",
 *         @OA\JsonContent(
*              @OA\Schema(
*                  type="object",
*              ),
*              @OA\Examples(
*                  example="Not your task",
*                  summary="Not your task",
*                  value={"message": "You don't own this task"},
*              ),
*              @OA\Examples(
*                  example="Parent is not your task",
*                  summary="Parent is not your task",
*                  value={"message": "You cannot add subtask to the task of another user"},
*              ),
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Undesired behaviour",
 *         @OA\JsonContent(
*              @OA\Property(
*                  property="message",
*                  type="object",
*                  example="You cannot attach task to its own subtask"
*              ),
 *         )
 *     ),
 * ),
 * @OA\Patch(
 *     path="/api/v1/tasks/{id}/status",
 *     summary="Updates a status of a task",
 *     tags={ "Tasks" },
 *     security={{ "bearerAuth": {} }},
 *     description="# Updates a status of a task/subtask for authenticated user
### Id
You must specify an id of a task you want to update in the URL.

### Json body
You must specify only one field 'status' as 'done' or 'todo'.<br>
You cannot mark the task as done if it has undone subtasks.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Specify an id of a task you wish to update",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="status",
 *                     type="string",
 *                     enum={"done", "todo"},
 *                     description="Possible values are 'done' and 'todo'"
 *                 ),
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items(
 *               @OA\Property(property="id", type="integer", example=22),
 *               @OA\Property(property="userId", type="integer", example=null),
 *               @OA\Property(property="parentId", type="integer", example=null),
 *               @OA\Property(property="status", type="string", example="done"),
 *               @OA\Property(property="priority", type="integer", example=4),
 *               @OA\Property(property="title", type="string", example="Clean the house"),
 *               @OA\Property(property="description", type="string", example="Clean everything in that house."),
 *               @OA\Property(
 *                   property="completedAt",
 *                   type="string",
 *                   format="date-time",
 *                   nullable=true,
 *                   example=null,
 *               ),
 *               @OA\Property(
 *                   property="createdAt",
 *                   type="string",
 *                   format="date-time",
 *                   example="29-10-2022 20:44:30",
 *               ),
 *             )),
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="If user is not logged in",
 *         @OA\JsonContent(
*              @OA\Property(
*                  property="message",
*                  type="object",
*                  example="Unauthenticated"
*              ),
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden operations",
 *         @OA\JsonContent(
*              @OA\Property(
*                  property="message",
*                  type="object",
*                  example="You don't own this task"
*              ),
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Undesired behaviour",
 *         @OA\JsonContent(
*              @OA\Property(
*                  property="message",
*                  type="object",
*                  example="You must complete all subtasks before moving on"
*              ),
 *         )
 *     ),
 * ),
 * @OA\Delete(
 *     path="/api/v1/tasks/{id}/delete",
 *     summary="Deletes user's task",
 *     tags={ "Tasks" },
 *     security={{ "bearerAuth": {} }},
 *     description="# Deletes a task/subtask of authenticated user
### Id
You must specify an id of a task you want to delete in the URL.

### Constraints
You cannot delete a completed task.<br>
You cannot delete a task with completed subtasks.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Specify an id of a task you wish to update",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *         @OA\JsonContent(
*              @OA\Property(
*                  property="message",
*                  type="object",
*                  example="Task with id 22 successfully deleted"
*              ),
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="If user is not logged in",
 *         @OA\JsonContent(
*              @OA\Property(
*                  property="message",
*                  type="object",
*                  example="Unauthenticated"
*              ),
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden operations",
 *         @OA\JsonContent(
*              @OA\Property(
*                  property="message",
*                  type="object",
*                  example="You don't own this task"
*              ),
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Undesired behaviour",
 *         @OA\JsonContent(
*              @OA\Schema(
*                  type="object",
*              ),
*              @OA\Examples(
*                  example="Delete a completed task",
*                  summary="Delete a completed task",
*                  value={"message": "You cannot delete a completed task"},
*              ),
*              @OA\Examples(
*                  example="Delete a task with completed subtasks",
*                  summary="Delete a task with completed subtasks",
*                  value={"message": "You cannot delete a task with completed subtasks"},
*              ),
 *         )
 *     ),
 * ),
 */
class SwaggerHelper extends Controller
{
    //
}
