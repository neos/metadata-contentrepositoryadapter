# Neos Neos MetaData ContentRepositoryAdapter

This package handels the mapping of meta data DTOs to the Neos Content Repository.

**Note: This package is work in progress. The class structure and interfaces may change a lot over time. The package is not meant for productive use.**

It provides two main features:

* Configurable mapping of the meta data DTOs to MetaData node types.
* Enables FlowQuery to query for assets by their meta data and to load the meta data for any existing asset object.

## FlowQuery Examples
Get all images of an author:

	collection = ${q(assets).children('[instanceof Neos.MetaData:Image][authorByline*="Daniel Lienert"]').get()}
	
Load the meta data of an asset node property - here of the property image:

	prototype(TYPO3.Neos.NodeTypes:Image) {
		metaData = ${q(node).metaData('image').properties}
	}
