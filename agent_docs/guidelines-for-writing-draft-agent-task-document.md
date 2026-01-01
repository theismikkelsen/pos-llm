# Guidelines For Writing Draft Agent Task Document

- When the human operator asks you to make a draft for an agent task document, your task is to take the operator's short description and turn it into a task that can be handed to an AI-agent.
- Save the document as `àgent_tasks/draft-task-{kebab-case-description}.md`.

## Example

### Query by human operator

```
make a draft task for this feature:
- add method on transfer of inventory items between receptacles ledger should verify that item instance and receptacles for inventory items exist.
- requires some other work first, InventoryItemAtLowestDistinctLevel and ReceptacleForInventoryItems need to be implemented

```

### Draft Agent Task Document Created By Agent 
```
The add-method on TransferOfInventoryItemsBetweenReceptaclesLedger should verify that inventoryItemAtLowestDistinctLevel and receptaclesForInventoryItems exist.

This requires some upstream work first, to implement classes for InventoryItemAtLowestDistinctLevel and ReceptacleForInventoryItems (implement only methods required to support change to TransferOfInventoryItemsBetweenReceptaclesLedger and required for tests).

## Implementation Details

### Testing

- InventoryItemAtLowestDistinctLevelRepositoryTest: Implement as integration test.
- ReceptacleForInventoryItemsRepositoryTest: Implement as integration test.

### Frontend

- This task requires no changes to the frontend layer.

### Backend

- InventoryItemAtLowestDistinctLevel: Implement as entity class.
- InventoryItemAtLowestDistinctLevelRepository: Implement as repository class.
- ReceptacleForInventoryItems: Implement as entity class.
- ReceptacleForInventoryItemsRepository: Implement as repository class.

### Database

- This task requires no changes to the database schema.
```
    
