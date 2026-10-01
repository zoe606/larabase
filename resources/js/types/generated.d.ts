declare namespace App.Enums {
    export type Gender = 'male' | 'female';
    export type HttpStatus = 200 | 201 | 204 | 400 | 401 | 403 | 404 | 405 | 409 | 422 | 429 | 500 | 503;
    export type Permission =
        | 'users-view'
        | 'users-create'
        | 'users-edit'
        | 'users-delete'
        | 'roles-view'
        | 'roles-create'
        | 'roles-edit'
        | 'roles-delete'
        | 'permissions-view'
        | 'permissions-create'
        | 'permissions-edit'
        | 'permissions-delete'
        | 'menus-view'
        | 'menus-create'
        | 'menus-edit'
        | 'menus-delete'
        | 'settings-view'
        | 'settings-edit'
        | 'audit-view'
        | 'backups-view'
        | 'backups-create'
        | 'backups-delete';
}
