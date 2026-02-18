# Project Registry API (Phase 1)

Canonical project records are exposed from ChatProjects so downstream apps can project from a single source of truth.

## Schema version

- `schema_version`: `2026-02-16.project-registry.v1`

## Endpoints

### `GET /api/projects`

Returns all active projects.

#### Query params

- `include_deleted=1` to include soft-deleted projects
- `updated_since=2026-02-16T00:00:00Z` for incremental sync

#### Response shape

```json
{
  "schema_version": "2026-02-16.project-registry.v1",
  "generated_at": "2026-02-16T18:15:00Z",
  "count": 2,
  "data": [
    {
      "id": 4,
      "project_uuid": "9abdcf57-7c34-4fdf-9b2d-bcff7f3782b0",
      "name": "Agile Medieval Peasant Board",
      "description": "...",
      "code_folder": null,
      "local_location": "/home/user/Code/Example",
      "github_repo": "https://github.com/org/repo",
      "gitea_location": null,
      "framework_description": "Laravel + Livewire",
      "languages": "PHP, JS",
      "created_at": "2026-02-14T10:00:00Z",
      "updated_at": "2026-02-16T17:45:00Z",
      "deleted_at": null
    }
  ]
}
```

### `GET /api/projects/{projectIdentifier}`

Returns one project by numeric `id` or `project_uuid`.

- Example: `/api/projects/4`
- Example: `/api/projects/9abdcf57-7c34-4fdf-9b2d-bcff7f3782b0`

Supports `include_deleted=1`.

## Notes

- `project_uuid` is immutable identity for cross-service references.
- Soft delete state is exposed via `deleted_at`.
- Downstream sync should use `updated_since` and upsert by `project_uuid`.
