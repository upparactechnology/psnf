# AI Implementation Rules and Guardrails

This document establishes the instructions, rules, design patterns, and quality checks that AI coding assistants must follow when implementing this codebase.

---

## 1. Core Development Guardrails

When generating or modifying source code for this project, AI coding assistants must adhere to the following rules:

* **Read Specifications First**: Before modifying any codebase files, review the relevant documentation in the `docs/` folder to understand design patterns, table schemas, and API contracts.
* **Strict Architecture Enforcement**: Do not simplify defined patterns. Use Clean Architecture, SOLID design principles, and Dependency Injection across all modules.
* **No Mocking or Placeholders**: All code changes must be production-ready. Do not write placeholder comments (e.g., `# TODO`) or mock implementations.
* **Zero Feature Creep**: Do not invent new database fields, API endpoints, or UI features that are not defined in these specifications.

---

## 2. Step-by-Step Implementation Workflow

To ensure system stability, AI assistants must build the project sequentially:

```
                  START WORK ON TARGET COMPONENT
                                │
                    Read specs and requirements
                                │
                      Implement code changes
                                │
                    Execute compiling checks
                                │
                      Run automated tests
                                │
                        Fix lint errors
                                │
                       COMMIT CHANGES & LOOP
```

1. **Read & Align**: Review the specifications for the target module.
2. **Implement Code**: Write complete, typed source code.
3. **Verify Execution**: Run build tools and verify compilation.
4. **Test**: Run automated tests to check for regressions.
5. **Lint**: Run linters (e.g., Flake8, Ktlint) to verify formatting standards before committing changes.

---

## 3. Architecture Guardrails

* **Data Access Separation**: All database operations must go through Repository classes. Web controllers must not query database engines directly.
* **Stateless API Routing**: Keep API endpoints stateless, validating authorization states via JWT tokens passed in request headers.
* **Asynchronous Operations**: Implement asynchronous operations for all database and network interactions to optimize throughput.

For the project layout details, refer to [25_PROJECT_STRUCTURE.md](25_PROJECT_STRUCTURE.md).
