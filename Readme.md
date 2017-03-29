[![Latest Stable Version](https://poser.pugx.org/neos/metadata-contentrepositoryadapter/v/stable)](https://packagist.org/packages/neos/metadata-contentrepositoryadapter) [![Total Downloads](https://poser.pugx.org/neos/metadata-contentrepositoryadapter/downloads)](https://packagist.org/packages/neos/metadata-contentrepositoryadapter) [![License](https://poser.pugx.org/neos/metadata-contentrepositoryadapter/license)](https://packagist.org/packages/neos/metadata-contentrepositoryadapter)

# Neos MetaData Content Repository Adapter

This package handles the mapping of meta data DTOs to the Neos Content Repository.

It provides three main features:

* Configurable mapping of the meta data DTOs to MetaData node types.
* FlowQuery operation to query for assets by their meta data.
* Eel helper to load the meta data nodes for any existing asset object.

## Installation
1. `composer require neos/metadata-contentrepositoryadapter`
2. Extract the meta data (f.e. with suggested `neos/metadata-extractor`)
3. Use the meta data in FlowQuery or Eel

## Examples

### Custom meta data node types
Create a NodeType inheriting from `Neos.MetaData:AbstractMetaData`. Most of the time you will be inheriting from `Neos.MetaData:Asset`.

    'Vendor.Namespace:Type':
      superTypes:
        'Neos.MetaData:Asset': true
      properties:
        yourProperty:
          mapping: '${yourDto.yourProperty}'


Specify the media types for which your NodeType will be used for.

    Neos:
      MetaData:
        ContentRepositoryAdapter:
          mapping:
            nodeTypeMappings:
              'type/subtype': 'Vendor.Namespace:Type'
              'type/otherSubtype': 'Vendor.Namespace:Type'


### Eel
Find the meta data of an asset - here of the node property `image`:

```
prototype(TYPO3.Neos.NodeTypes:Image) {
    imageMetaDataNode = ${MetaData.find(q(node).property('image'), node)}
    imageTitle = ${q(this.imageMetaDataNode).property('title')}
}
```

### FlowQuery
Get all meta data nodes matching the filter:

```
collection = ${q(assets).children('[instanceof Neos.MetaData:Exif][artist*="Daniel Lienert"]').get()}
```

Get the assets referenced by those meta data nodes:
```
assets = ${q(this.collection).getAssets()}
```
