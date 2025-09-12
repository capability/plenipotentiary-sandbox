# Epic E4: Observability (Baseline)

**Goal:** Provide minimal observability hooks for developers.

## Scope

- Structured logging with correlation IDs
- Error logging via configured Laravel channel
- Optional OpenTelemetry spans for outbound HTTP
- Basic metrics counters (calls, errors, retries, rate-limit waits)

## Out of Scope

- Full tracing UI integration
- App-level dashboards

## Deliverables

- [ ] Log correlation ID helper
- [ ] Logs routed to Laravel log channel
- [ ] Config toggle for OpenTelemetry spans
- [ ] Middleware emitting retry + rate-limit metrics

## Risks

- Complexity of OTel integration
- Logging noise if defaults not sensible
