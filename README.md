# XPath Scraper & Query Builder Bundle

A Symfony bundle for extracting data from HTML using expressive, composable XPath queries. This bundle provides a fluent API for building XPath selectors and scraping content, with a focus on testability and developer ergonomics.

---

## Features

- Build complex XPath queries with a fluent, type-safe API
- Extract text, HTML, attributes, or custom data from HTML documents
- Chain queries to traverse and filter DOM nodes
- Designed for integration with Symfony and modern PHP
- **NEW:** Use CSS selectors directly with the [CSS Query Builder](README.CssQueryBuilder.md)
	- Direct child combinator (`>`) now supported
	- Pseudoclass selectors like `:nth-child(N)` now supported

---

## Installation


1. **Add the VCS to your composer file:**
	```json
	"repositories": [
		 {
			  "type": "vcs",
			  "url": "https://github.com/tbessenreither/xpath-scraper"
		 }
	]
	```

2. **Install the package via composer:**
	```
	composer require tbessenreither/xpath-scraper
	```

2. **Register the bundle in `config/bundles.php`:**
   ```php
   return [
	   // ...
	   Tbessenreither\XPathScraper\Bundle\XPathScraperBundle::class => ['all' => true],
   ];
   ```

---

## Query Builder

For advanced usage and API details, see [README.QueryBuilder.md](README.QueryBuilder.md).

## Usage

### 1. Scrape HTML with expressive queries

Inject or instantiate the `Scraper` class with your HTML:

```php
$scraper = new Scraper($html);
```

Build queries using the query builder classes:

```php
use Tbessenreither\XPathScraper\QueryBuilder\Service\QueryBuilder;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryElement;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\LogicWrapper;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryClass;

$mainContent = $scraper->get(new QueryBuilder([
	new QueryElement('div', [
		new LogicWrapper(LogicWrapper::OR, [
			new QueryClass('outer', QueryClass::EXACT),
			new QueryClass('container-', QueryClass::STARTS_WITH),
		])
	])
]));

$footer = $mainContent->get(new QueryBuilder([
	new QueryElement('div', [
		new QueryClass('footer', QueryClass::EXACT),
	])
]));

$links = $footer->get(new QueryBuilder([
	new QueryElement('a', [
		new QueryClass('link', QueryClass::CONTAINS),
	])
]));

$extractions = $links->extract([
	Scraper::EXTRACT_ATTRIBUTE_PREFIX . 'href',
	Scraper::EXTRACT_TEXT,
]);

foreach ($extractions as $extraction) {
	$href = $extraction->getAttribute('href');
	$text = $extraction->getText();
	// ...
}
```


## Usage Example: CSS Query Builder

You can now use CSS selectors directly:

```php
use Tbessenreither\XPathScraper\QueryBuilder\Service\CssQueryBuilder;

$builder = new CssQueryBuilder('div.outer');
$xpath = $builder->getXPathSelector();
// $xpath now contains the XPath for div.outer
```

See [README.CssQueryBuilder.md](README.CssQueryBuilder.md) for more details and advanced usage, including direct child and pseudoclass selectors.

## Requirements

- PHP ^8.4
- symfony/dom-crawler (optional, for advanced use)

---

## License

MIT

---

## Project Structure & Architecture

- **src/**: Main source code
	- **Bundle/**: Bundle registration and DI configuration
	- **Dto/**: Data transfer objects for extraction results
	- **QueryBuilder/**: Query builder and selector logic
	- **Service/**: Scraper logic
- **tests/**: PHPUnit test suite and fixtures
- **composer.json**: Dependency and requirement definitions