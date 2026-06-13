# Engineering Rules

1. Every module is independent.

2. Every module owns its business logic.

3. Shared database only through services.

4. No hard deletes.

5. Use UUIDs.

6. Every table must include:

* id
* created_at
* updated_at
* created_by
* updated_by

7. Audit logs mandatory.

8. Permissions required on every endpoint.

9. Multi-school support mandatory.

10. Every module exposes APIs.

11. Every module supports reporting.

12. Document exports must support PDF.
