# Workflow: Make Feature/Change Specification Document

## Workflow Objective

To produce a clear and well-structured specification document that describes a new feature or change to the application (hereafter *the spec*) by iteratively asking the operator questions about the feature/change until no significant ambiguity remains. When done, the finished spec should clearly and unambiguously describe *what* the feature/change is (without describing *how* it should be implemented from a technical point of view).

## When To Use This Workflow

Only use this workflow when the operator explicitly initiates it. Confirm with operator if unsure.

## Workflow Steps

### Step 1: Perform Initial Research And Create WIP document

- Perform the initial research in the codebase that you require to understand the context of the feature/change.
- Create folder using naming pattern `agent_tasks/feature-or-change-{short-kebab-case-description}` and in that folder create a file named `spec-wip.md`.
- Make the initial content for the spec by parsing the operator's initial description of the feature/change. 
	- Assume the initial description from the operator to be provisional or incomplete (unless specified otherwise) and use it only as a starting point to explore the actual requirements and scope of the feature/change.
	- Use *TBDs* and/or similar terms to indicate where information or elaboration is required.

### Step 2: Ask Operator Questions And Iterate On The Feature Spec Document

- Ask the operator questions until the spec clearly and fully describes the feature/change. 
- Ask a few related questions at a time, rather than asking all open questions in one go.
- Ask the questions in a way that makes it easier for the operator to answer them: Number questions, express them clearly and concisely and suggest sensible default answers if possible.
- Update the WIP document as you gain new information from the operator, updating/adding/removing TBDs as questions arise or are resolved during the interview. 
- Continuously organize the document to maintain clarity and conciseness. 
- Parse the operator's answer and add them to the spec in an organized manner, rather than simply appending raw notes.

### Step 3: Conclude Interview

- Conclude the interview only when you believe that all significant ambiguity regarding the feature and its functionality has been resolved and no TBDs remain. Explicitly verify with the operator that the spec is complete before ending the session.
- End by renaming the spec file from this workflow from `spec-wip.md` to `spec.md`.

## How The Spec Should Be Structured And Written

- Write the spec to be clear and concise, with high signal and low noise.
- Organize the spec to be highly scannable.
	- Default to bullet points. Every requirement must be a standalone bullet point.
	- Use hierarchical structure. Use nested bullets for sub-details or edge cases.
 	- Write edit-friendly markdown: Avoid excessive styling or complex tables that are difficult for a human to modify quickly.	
- All requirements and specifications must align and be non-contradictory. Ask questions to clarify contradictions or ambiguity. If operator provides conflicting information, point it out and ask questions to resolve it.
- Use this high-level structure for the spec:
  ```markdown
  # Feature/Change Specification: [Title]

  ## Executive Summary

  [Brief overview of the change and its context.]

  ## Objectives & Impact

  [The intended outcomes and the primary value this change provides.]

  ## Requirements & Specifications

  [Organized list of standalone requirements. Use nesting for sub-details.]

  ## Constraints & Non-Functional Requirements

  [Boundaries or quality standards, such as performance, security, or platform limits.]

  ## Scope Clarifications

  [Specific items, features, or behaviors explicitly excluded from this change.]
  ```

## Workflow Constraints

- Do not ask about or describe technical details that only concern *how* the feature/change is to be implemented (e.g. files, classes, functions, database tables, etc.).
- Do not edit or write any files other than the spec.
- Do not read previous feature specifications unless explicitly asked to.

## Context: How This Workflow Fits Into The Overarching Process Of Implementing Feature/Change

This workflow intentionally is only concerned with the initial stage of implementing a new feature: specifying *what* the feature/change is. Another workflow will be used after this one to create a technical implementation plan describing *how* the feature/change should be implemented.
