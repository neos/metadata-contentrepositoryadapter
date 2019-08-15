[![Latest Stable Version](https://poser.pugx.org/neos/metadata-contentrepositoryadapter/v/stable)](https://packagist.org/packages/neos/metadata-contentrepositoryadapter)
[![Total Downloads](https://poser.pugx.org/neos/metadata-contentrepositoryadapter/downloads)](https://packagist.org/packages/neos/metadata-contentrepositoryadapter)
[![License](https://poser.pugx.org/neos/metadata-contentrepositoryadapter/license)](https://packagist.org/packages/neos/metadata-contentrepositoryadapter)

# Neos.MetaData.ContentRepositoryAdapter Package

This package handles the mapping of meta data DTOs to the Neos Content Repository.

It provides three main features:

* Configurable mapping of the meta data DTOs to MetaData node types.
* FlowQuery operation to query for assets by their meta data.
* Eel helper to load the meta data nodes for any existing asset object.

## Installation

Install using composer:

    composer require neos/metadata-contentrepositoryadapter  

Some related packages are:

- [`neos/metadata`](https://github.com/neos/metadata): Provides provides data types and interfaces
  (and is automatically installed with this package)
- [`neos/metadata-extractor`](https://github.com/neos/metadata-extractor): Provides CLI and realtime
  meta data extraction on assets

## Configuration

The package provides a way to store asset meta data in nodes, so it can be used in an application.

## Usage

The package does not in itself change the way metadata is handled. Instead it provides ways for
other packages to interact with meta data of assets.

1. Install the package
2. Extract the meta data (f.e. with suggested `neos/metadata-extractor`)
3. Use the meta data in FlowQuery or Eel

## Examples

### Custom meta data node types

Create a NodeType inheriting from `Neos.MetaData:AbstractMetaData`. Most of the time you will be
inheriting from `Neos.MetaData:Asset`.

```yaml
'Vendor.Namespace:Type':
  superTypes:
    'Neos.MetaData:Asset': true
  properties:
    yourProperty:
      mapping: '${yourDto.yourProperty}'
```

Specify the media types for which your NodeType will be used for.

```yaml
Neos:
  MetaData:
    ContentRepositoryAdapter:
      mapping:
        nodeTypeMappings:
          'type/subtype': 'Vendor.Namespace:Type'
          'type/otherSubtype': 'Vendor.Namespace:Type'
```

### Eel

Find the meta data of an asset - here of the node property `image`:

```
prototype(Neos.NodeTypes:Image) {
    imageMetaDataNode = ${MetaData.find(q(node).property('image'), node)}
    imageTitle = ${q(this.imageMetaDataNode).property('title')}
}
```

### FlowQuery

Get all meta data nodes matching the filter:

    collection = ${q(assets).children('[instanceof Neos.MetaData:Exif][artist*="Daniel Lienert"]').get()}

Get the assets referenced by those meta data nodes:

    assets = ${q(this.collection).getAssets()}
