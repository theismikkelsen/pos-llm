# Task A

Create migrations for these 5 tables. The columns are described in casual language, and you should interpret what I actually meant by the line. Ask clarifying questions if necessary. Do no run the migrations.

inventory_item_definitions
- id
- sku_id
- name
- is_lot_tracked
- is_serial_tracked
- timestamps

inventory_item_instances
- id
- inventory_item_definition_id
- lot_number
- serial_number
- quantity (signed big integer)
- container_id (nullable)
- expiry_time (timestamp nullable)
- status
- timestamps

inventory_transactions
- id
- inventory_item_instance_id
- type
- quantity (signed big integer)
- from_lpn (nullable)
- to_lpn (nullable)
- from_location (nullable)
- to_location (nullable)
- timestamps

locations
- id
- code
- type
- timestamps

containers
- id
- lpn_code
- location_id (nullable)
- parent_container_id (nullable)
- timestamps
