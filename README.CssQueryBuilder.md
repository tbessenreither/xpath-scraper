
# CSS Query Builder

The CSS Query Builder allows you to construct XPath queries using familiar CSS selector syntax. It parses CSS selectors and builds the corresponding QueryBuilder tree, making it easier to write complex queries for scraping or DOM traversal.

## New Features

- **Direct child combinator (`>`) is now supported.**
- **Pseudoclass support:** Use `:nth-child(N)` to select elements by position.
- Class selectors support the following prefixes:
	- `.class` (exact match)
	- `.^class` (starts with)
	- `.$class` (ends with)
	- `.~class` (contains)

## Features
- Supports tag, class, attribute, and pseudoclass selectors
- Handles descendant and child combinators
- Converts CSS selectors to QueryBuilder objects and XPath

## Usage Example

```php
use Tbessenreither\XPathScraper\QueryBuilder\Service\CssQueryBuilder;

// Instead of:
// $builder = new QueryBuilder([
//     new QueryElement('div', [new QueryClass('outer', QueryClass::EXACT)])
// ]);

// You can use:
$builder = new CssQueryBuilder('div.outer');
$xpath = $builder->getXPathSelector();
// $xpath now contains the XPath for div.outer
```

---

## Supported Selectors and Combinators

### 1. Tag Selector
**CSS:** `div`
```php
$builder = new CssQueryBuilder('div');
// QueryBuilder tree:
// [ new QueryElement('div') ]
```

### 2. Descendant Selector
**CSS:** `div a`
```php
$builder = new CssQueryBuilder('div a');
// QueryBuilder tree:
// [ new QueryElement('div'), new QueryElement('a') ]
```

### 3. Class Selector
**CSS:** `div.outer`
```php
$builder = new CssQueryBuilder('div.outer');
// QueryBuilder tree:
// [ new QueryElement('div', [ new QueryClass('outer', QueryClass::EXACT) ]) ]
```

### 4. Attribute Selector
**CSS:** `a[href="test"]`
```php
$builder = new CssQueryBuilder('a[href="test"]');
// QueryBuilder tree:
// [ new QueryElement('a', [ new QueryAttribute('href', 'test', QueryAttribute::EXACT) ]) ]
```

### 5. Multiple Classes
**CSS:** `div.outer.highlight`
```php
$builder = new CssQueryBuilder('div.outer.highlight');
// QueryBuilder tree:
// [ new QueryElement('div', [
//     new QueryClass('outer', QueryClass::EXACT),
//     new QueryClass('highlight', QueryClass::EXACT)
// ]) ]
```

### 6. Multiple Attributes
**CSS:** `a[href="test"][target="_blank"]`
```php
$builder = new CssQueryBuilder('a[href="test"][target="_blank"]');
// QueryBuilder tree:
// [ new QueryElement('a', [
//     new QueryAttribute('href', 'test', QueryAttribute::EXACT),
//     new QueryAttribute('target', '_blank', QueryAttribute::EXACT)
// ]) ]
```

### 7. Child Combinator
**CSS:** `ul > li`
```php
$builder = new CssQueryBuilder('ul > li');
// QueryBuilder tree:
// [ new QueryElement('ul'), new QueryElement('li', isDirectChild: true) ]
// (interpreted as direct child in XPath)
```

### 11. Pseudoclass Selector
**CSS:** `li:nth-child(2)`
```php
$builder = new CssQueryBuilder('li:nth-child(2)');
// QueryBuilder tree:
// [ new QueryElement('li', nthChild: 2) ]
// XPath: //li[position()=2]
```

### 8. Grouping (Comma)
**CSS:** `div, span`
```php
$builder = new CssQueryBuilder('div, span');
// QueryBuilder tree:
// [ new LogicWrapper(LogicWrapper::OR, [
//     new QueryElement('div'),
//     new QueryElement('span')
// ]) ]
```

### 9. Attribute Starts With / Ends With
**CSS:** `a[href^="https"], a[href$=".pdf"]`
```php
$builder = new CssQueryBuilder('a[href^="https"], a[href$=".pdf"]');
// QueryBuilder tree:
// [ new LogicWrapper(LogicWrapper::OR, [
//     new QueryElement('a', [ new QueryAttribute('href', 'https', QueryAttribute::STARTS_WITH) ]),
//     new QueryElement('a', [ new QueryAttribute('href', '.pdf', QueryAttribute::ENDS_WITH) ])
// ]) ]
```

### 10. Class Modifiers (Prefix Syntax)

**CSS:** `div.^foo`, `div.$bar`, `div.~baz`

```php
$builder = new CssQueryBuilder('div.^foo');
// QueryBuilder tree:
// [ new QueryElement('div', [ new QueryClass('foo', QueryClass::STARTS_WITH) ]) ]

$builder = new CssQueryBuilder('div.$bar');
// QueryBuilder tree:
// [ new QueryElement('div', [ new QueryClass('bar', QueryClass::ENDS_WITH) ]) ]

$builder = new CssQueryBuilder('div.~baz');
// QueryBuilder tree:
// [ new QueryElement('div', [ new QueryClass('baz', QueryClass::CONTAINS) ]) ]
```

---

## When to Use
- When you want to write queries using CSS selector syntax
- For rapid prototyping and easier query writing

See the test suite for more supported selectors and advanced usage.
