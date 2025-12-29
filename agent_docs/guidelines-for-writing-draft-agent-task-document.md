# Guidelines For Writing Draft Agent Task Document

- When the human operator asks you to make a draft for an agent task document, your task is to take the operator's short description and turn it into a task that can be handed to an AI-agent.
- Save the document as `àgent_tasks/draft-task-{kebab-case-description}.md`.

## Example

### Query by human operator

```
make a draft task for this feature:
- add method on inventory movement ledger should verify that item instance and inventory locations exists.
- requires some other work first, InventoryItemInstance and InventorLocation need to be implemented

```

### Draft Agent Task Document Created By Agent 
```
The add-method on InventoryMovementLedger should verify that inventoryItemInstance and inventoryLocations exist. 

This requires some upstream work first, to implement classes for InventoryItemInstance and InventorLocation (implement only methods required to support change to InventoryMovementLedger and required for tests).

## Implementation Details

### Testing

- InventoryItemInstanceRepositoryTest: Implement as integration test.
- InventorLocationRepositoryTest: Implement as integration test.

### Frontend

- This task requires no changes to the frontend layer.

### Backend

- InventoryItemInstance: Implement as entity class.
- InventoryItemInstanceRepository: Implement as repository class.
- InventorLocation: Implement as entity class.
- InventorLocationRepository: Implement as repository class.

### Database

- This task requires no changes to the database schema.
```
    
