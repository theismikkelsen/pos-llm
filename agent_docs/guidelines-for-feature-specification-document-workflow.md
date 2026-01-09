# Guidelines For Feature Specification Document Workflow

## Workflow Goal

The goal of this workflow is to develop a comprehensive feature specification document that provides sufficient detail for an AI agent to implement the feature autonomously in the future.

## Acceptance Criteria For Feature Specification Document

- The document fully and precisely describes the functionality of the feature.
- The document details how the feature integrates with the application and codebase, correctly referencing routes, existing structures, and terminology from agent documentation and the source code.

## Workflow

### 1. Human Operator Initiates Workflow

- The human operator starts the process by indicating the intent to develop a feature specification document.
- The operator provides initial information to bootstrap the workflow. The agent should expect potentially sparse or unrefined information as initial input; the AI agent is responsible for parsing the input, interpreting the operator’s likely intent, and asking clarifying questions if necessary.
- The AI agent creates a markdown file within a subfolder og the `agent_tasks` folder, using the naming convention `feature-{description}/wip-feature-spec.md`.
- The AI agent researches relevant agent documentation and source code, to better understand the context of the feature.

### 2. AI Agent Iteratively Interviews The User About Feature

- The AI agent conducts an exhaustive inquiry and continues until all necessary information is gathered. Multiple rounds of questioning is encouraged if it helps produce a better feature specification.
- Ask questions in a way so that the human operators can answer effectively: Label questions with numbers. When relevant, provide suggested answers. Be concise.
- do not truncate the process for the sake of brevity; the operator will explicitly signal if he feels that it is time to conclude the interview.


### 3. Conclusion Of The Workflow

- The process concludes when either the AI agent or the human operator determines that all required information has been gathered. If the AI agent suggests concluding the interview, it must first verify with the human operator.
- Upon conclusion, the agent renames the file to `feature-spec.md`.

## Template For Feature Specification Document

```md
# Feature Specification: ...

## Objective

### Problem Statement

...

### Proposed Solution (High-Level)

...

## Requirements

...

### Functional Requirements

...

### Non-Functional Requirements

...

## Testing Requirements

### Unit Tests

...

### Integration Tests

...

### Feature Tests

...

### Browser-Based Smoke Tests

...

## Scope Boundaries

...

### In Scope

...

### Out of Scope

...
```
