# Unit of work

This library provides tooling for post-processing. Lets explain it on the example:

You have some domain entity (lets call it a `Document`). And in your logic you do a lot of
different changes and data transformations and you want to persist those changes
for example into ElasticSearch database. 

The problem is that if you save it right in place after the change, you may end up 
with multiple writes.

By using this library you can track all of those changes and merge them.

```php
function doBoringStuff(Document $document): UnitOfWork {
	$document->changeBoringStuff();
	$unitOfWork = new UnitOfWork();
	$unitOfWork->registerOperation(new SaveDocumentOperation($document));
	
	return $unitOfWork;
}

function doFunkyStuff(Document $document): UnitOfWork {
	$document->changeFunkyStuff();
	$unitOfWork = new UnitOfWork();
	$unitOfWork->registerOperation(new SaveDocumentOperation($document));
	
	return $unitOfWork; 
}


$unitOfWork = doBoringStuff($document);
$unitOfWork->merge(doFunkyStuff($document));

// Every Processor has its own Accessor for Dependecy Injection Container
$accessors = [new SaveDocumentOperationProcessorAccessor()]; 

$naiveExecutor = new NaiveUnitOfWorkExecutor($accesors);
$executor = new ReducingUnitOfWorkExecutor($naiveExecutor); // Decorator pattern to separate merging logic

$executor->execute($unitOfWork);
```

Example of usage inside Nette Dependecy Injection container:
```yaml
unitOfWorkExecutor: BrandEmbassy\UnitOfWork\ReducingUnitOfWorkExecutor(
    BrandEmbassy\UnitOfWork\NaiveUnitOfWorkExecutor([@saveDocumentOperationProcessorAccessor])
)

- Foo\Bar\SaveDocumentOperationProcessor
saveDocumentOperationProcessorAccessor: Foo\Bar\SaveDocumentOperationProcessorAccessor
```
