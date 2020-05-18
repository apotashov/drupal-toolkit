# Drupal Toolkit module

## Overview

This Drupal module is aimed as providing a collection of useful development-centric components aimed mostly, but not limited to, entity type creation, management, and decoupling.

## Contents

The areas below list all included functionality and components.

### Classes (entity)

- `ContentEntityAccessControlHandler.php`: Access control handler for content entities that can be used for most cases, or extended upon. Includes ability to easily restrict given fields to admin-only.
- `ContentEntityHtmlRouteProvider.php`: Html route provider for content entities that can easily provide a defined settings form, support for revisionable entities, and the ability to restrict canonical access so that entities can be returned via API but direct access restricted to editors/admins.
- `ContentEntityListBuilder.php`: More usable version of `EntityListBuilder`.
- `ContentEntityRevisionStorage.php`: Flexible entity storage handler for revisionable entities.

### Classes (misc)

- `JsonApiResponseControllerBase.php`: Base controller class to provider a controller that can return a JSON:API response with arbitrary entities and data.
- `JsAppElement.php`: A simple way to standardize the creation of DOM elements used to initialize JS apps, which can contain JSON:API entity data and arbitrary settings.

### Traits

- `EntityContextualTrait.php`: Indicates that this entity type should be considered contextual in conditions more than just viewing the canonical route. See `ContextualEntity`.
- `EntityCreatedTrait.php`: Get/set functions for entity creation time.
- `EntityExternalIdTrait.php`: Get/set functions for a defined field that stores the entity external ID. Useful if entities are synced from external sources.
- `EntityLastUpdatedByTrait.php`: Automatically track the user who last updated the entity.
- `EntityUuidRouteTrait.php`: Allows for the UUID to be used in entity routes, rather than the entity ID.
- `EntityParentTrait.php`: Defines a hierarchy between entities and allows for easy traversing. Extends the `ContextualEntity` functionality to automatically derive related entities.
- `EntityPublishedDateTrait.php`: Store the date when the entity was first published.
- `EntityUrlIdTrait.php`: Auto-generates a URL ID which can be used as an SEO-friendly route-parameter, rather than entity ID, and bypasses the need for extension path aliases. See class for a full explanation.

### Controllers

- `ContentEntityRevisionController.php`: A generic controller providing route callbacks for revisionable entities. See `ContentEntityHtmlRouteProvider.php`.

### Forms

- `RevisionableContentEntityForm.php`: A generic content entity form for revisionable entities.
- `RevisionDeleteForm.php`: A generic content entity delete form for revisionable entities.
- `RevisionRevertForm.php`: A generic content entity revert form for revisionable entities.

### Services

- `toolkit.contextual_entity`: Determines the primary entities which make up the context of the given request, and transmits the data to the front-end via Drupal JS settings. The purpose of this is to aid in decoupling, giving the front-end knowledge of the context of the given page. This functionality is enhanced if the entity types are using the classes provided by this module, and leveraging the parent-relationship linking of entity types, in order to determine hierarchy. See `ContextualEntity.php` and `toolkit_page_attachments_alter()`.
- `toolkit.jsonapi_generator`: Create JSON:API markup using arbitrary entities, metadata, etc. Many plugins and classes leverage this.
- `toolkit.time`: An extension of core's time service, with additional functions for things like timezone conversions.

### ParamConverters

- `entity_field_value`: Convert a (unique) field value to an entity for any specified field, entity type, and optional bundle.
- `entity_url_id`: Convert a URL ID to an entity for any specified entity type, and optional bundle. See `EntityUrlIdTrait`.
- `entity_uuid`: Convert a UUID to an entity.

### Logger

- `MailLog`: Email log entries that exceed warning status when they occur. Developers can force alerts for any log entry if `mail_log` is passed in as a contextual variable.

### Plugins

#### Condition

- `entity_type_canonical`: Check if the current request is the canonical route of an entity for a given entity type.

#### jsonapi\FieldEnhancer

- `json_string_to_array`: Convert a string of JSON to a structured array.
- `link_url`: Include the absolute URL alongside the URI.

#### Field\FieldFormatter

- `entity_reference_json_api`: Generic field formatter for rendering JSON:API markup of referenced entities within a `JsAppElement`.

#### Validation\Contraint

- `UniqueEntityReferenceField`: Validates that an entity reference field is unique for the given entity type.
- `UniqueMultiValueField`: Prevents a multi-value field from containing the same values within itself and other entities.

#### Mail

- `logger_mail`: Store all outbound emails in watchdog rather than sending.

#### migrate\Process

- `array_value`: Treats an array as a single value, passing that directly in to the field rather than having to use a sub-process.
- `debug`: Outputs the value to the console.
- `delete_entity_if`: Delete the given entity if the field value equates to true.
- `entity_random`: Fetches a random entity for a given bundle.
- `image`: Downloads a remote image and converts to a file entity.
- `in_array`: Checks if a value exists in an array.
- `negate`: Negate a value.
- `skip_row_if_value_is`: Skips processing the current row when a source value is equal to a given value.
- `skip_row_if_value_is_not`: Skips processing the current row when a source value is not equal a given value.
- `sub_process_format`: Formats a flat array to be used with a sub-process.
- `url_add_protocol_if_missing`: Add a protocol to the URL if it's not set.

#### views\filter

- `entity_reference_autocomplete`: Provides entity reference autocomplete filters for views.

### Functions

Located in `toolkit.utility.inc`, which is automatically loaded.

- `module_update_config()`: Import a given yml config file inside a given module.
- `revert_feature()`: Revert a given feature module.
- `entity_base_field_install()`: Install an entity base field.
- `entity_base_field_uninstall()`: Uninstall an entity base field.
- `entity_get_file_url()`: Helper function to easily get the URL of a file referenced in a given field of a given entity.
- `entity_is_publishable()`: Determine if an entity can be published and unpublished.
- `entity_has_live_change()`: Determine if an entity is experiencing a change that affects the 'live' (published) site.
- `entity_uses_trait()`: Determine if an entity is using a given trait.

### Additional functionality

- **Image proxy**: Provides admin setting to specify a domain which all images will be served from.
- **Entity reference field entity edit links**: Provides an edit button next to entity reference fields once they contain an entity reference.
- **Entity list per-bundle cache tag invalidation**: Invalidates entity per-bundle cache tags, ie, `node_list:article`.
- **Entity query random sorting**: Support for random entity query sorting by adding tag `sort_by_random`.
- **Performance improvements to entity queries**: see `EntityQueryTables.php`.

### Scripts

- `settings.php`: Useful snippets for including in settings.php.

### Examples

See the `toolkit_examples` module located within `modules`. Still in progress.
