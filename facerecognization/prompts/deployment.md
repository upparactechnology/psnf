# AI Prompt: DevOps, Containerization & Deployment Setup

Use this prompt to instruct an AI assistant to implement the deployment configuration files:

```markdown
## Objective
Configure production deployment environments, multi-container configurations (Docker Compose), Nginx routing profiles, SSL setup scripts, and system security rules for hosting the application on an Ubuntu VPS.

## Requirements
1. **Docker Compose Configuration**:
   - Set up three isolated services: `database` (MySQL 8.0), `backend` (FastAPI), and `nginx` (web server proxy).
   - Ensure database data is persisted using Docker volumes.
   - Configure container restart rules (`restart: always`) to recover from host restarts.
2. **Nginx Reverse Proxy & SSL**:
   - Map public port `80` to redirect HTTP requests to HTTPS (`443`).
   - Configure HTTPS with TLS 1.3 protocol validation.
   - Set up routing rules: serve built static React files directly from the root location `/`, and route dynamic `/api/v1/` requests to the FastAPI backend service.
3. **SSL Generation Script**:
   - Provide shell script instructions using Certbot to obtain free Let's Encrypt SSL certificates.

## Target Folder Structure
```
docker/
├── nginx/
│   └── conf.d/
│       └── default.conf                # Nginx proxy routing configuration
└── mysql/
    └── my.cnf                          # Custom MySQL performance settings
docker-compose.prod.yml                  # Production Compose file
```

## Expected Deliverables
1. Configuration files (`docker-compose.prod.yml`, `default.conf`, and `my.cnf`).
2. Script instructions to set up the firewall and run the environment.

## Acceptance Criteria
- Running `docker compose -f docker-compose.prod.yml up -d` builds and launches all containers.
- Accessing the public domain shows the dashboard with secure SSL encryption, routing API requests correctly.
```
