## Visual Example: What Does This Query Fetch?


The Query Builder allows you to create highly complex, deeply nested XPath queries in a structured, readable, and type-safe way. You can combine elements, class and attribute selectors, and logical wrappers (AND/OR) to express almost any selection logic.


**What are we building?**


Suppose your HTML looks like this:

```html
<div class="outer">
    <div data-role="main" data-active="true">
        <a class="link" href="/home">Home</a>
        <a class="link" href="/about">About Us</a>
    </div>
    <span data-role="main" data-active="true">
        <a class="link" href="/home">Home</a>
        <a class="link" href="/about">About Us</a>
    </span>
</div>
```

We want to match all `<a>` elements with class `link` that are:

- Descended from a `<div>` with class `outer` or a class starting with `container-`
- Whose closest parent matching the query is a `<div>` or `<span>` with `data-role="main"` and `data-active="true"`
- And where the `<a>`'s `href` either starts with `/home` OR starts with `/about` and ends with `/us`

The query does not select the parent tags themselves, but matches `<a>` elements that meet all these criteria in the DOM structure.
## Building Advanced XPath Queries

Now we get to the code that builds this complex XPath query using the Query Builder.

### Example: Complex Query

```php
use Tbessenreither\XPathScraper\QueryBuilder\Service\QueryBuilder;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryElement;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\LogicWrapper;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryClass;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryAttribute;

$builder = new QueryBuilder([
    new QueryElement('div', [
        new LogicWrapper(LogicWrapper::OR, [
            new QueryClass('outer', QueryClass::EXACT),
            new QueryClass('container-', QueryClass::STARTS_WITH),
        ])
    ]),
    new LogicWrapper(LogicWrapper::OR, [
        new QueryElement('div', [], [
            new LogicWrapper(LogicWrapper::AND, [
                new QueryAttribute('data-role', 'main', QueryAttribute::EXACT),
                new QueryAttribute('data-active', 'true', QueryAttribute::EXACT),
            ])
        ]),
        new QueryElement('span', [], [
            new LogicWrapper(LogicWrapper::AND, [
                new QueryAttribute('data-role', 'main', QueryAttribute::EXACT),
                new QueryAttribute('data-active', 'true', QueryAttribute::EXACT),
            ])
        ]),
    ]),
    new QueryElement('a', [
        new LogicWrapper(LogicWrapper::AND, [
            new QueryClass('link', QueryClass::CONTAINS),
        ])
    ], [
        new LogicWrapper(LogicWrapper::OR, [
            new QueryAttribute('href', '/home', QueryAttribute::STARTS_WITH),
            new LogicWrapper(LogicWrapper::AND, [
                new QueryAttribute('href', '/about', QueryAttribute::STARTS_WITH),
                new QueryAttribute('href', '/us', QueryAttribute::ENDS_WITH),
            ]),
        ])
    ]),
]);

// Get the resulting XPath string
$xpath = $builder->getXPathSelector();


// Resulting XPath:
//
//   //div[(contains(concat(' ', normalize-space(@class), ' outer ' ) or starts-with(normalize-space(@class), 'container-')))]
//   /(div[(@data-role='main' and @data-active='true')] or span[(@data-role='main' and @data-active='true')])
//   /a[(contains(@class, 'link'))][(starts-with(@href, '/home') or (starts-with(@href, '/about') and substring(@href, string-length(@href) - string-length('/us') + 1) = '/us'))]

```

This approach lets you build selectors that would be error-prone or unreadable as plain strings, while keeping your code maintainable and testable.
# XPath Query Builder

A powerful, composable PHP query builder for generating complex XPath selectors. Designed for use with the XPath Scraper bundle, but can be used standalone for any DOM or XML processing task.

---

## Features

- Fluent, object-oriented API for building XPath queries
- Supports logical wrappers (AND/OR), class and attribute selectors
- Type-safe and IDE-friendly
- Easily extendable for custom selector logic

---

## Usage Examples

### Basic Query

```php
use Tbessenreither\XPathScraper\QueryBuilder\Service\QueryBuilder;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryElement;

$builder = new QueryBuilder([
    new QueryElement('div'),
]);

$xpath = $builder->getXPathSelector();
// Result: //div
```

### Class and Attribute Selectors

```php
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryClass;
use Tbessenreither\XPathScraper\QueryBuilder\Selector\QueryAttribute;

$builder = new QueryBuilder([
    new QueryElement('a', [
        new QueryClass('link', QueryClass::CONTAINS),
    ], [
        new QueryAttribute('href', '/home', QueryAttribute::STARTS_WITH),
    ]),
]);

$xpath = $builder->getXPathSelector();
// Result: //a[contains(@class, 'link')][starts-with(@href, '/home')]
```

### Logical Wrappers

```php
use Tbessenreither\XPathScraper\QueryBuilder\Selector\LogicWrapper;

$builder = new QueryBuilder([
    new QueryElement('div', [
        new LogicWrapper(LogicWrapper::OR, [
            new QueryClass('outer', QueryClass::EXACT),
            new QueryClass('container-', QueryClass::STARTS_WITH),
        ])
    ])
]);

$xpath = $builder->getXPathSelector();
// Result: //div[(contains(concat(' ', normalize-space(@class), ' '), ' outer ' ) or starts-with(normalize-space(@class), 'container-'))]
```

---

## API Reference

- `QueryBuilder` — Main entry point for building queries
- `QueryElement` — Represents a tag and its class/attribute logic
- `QueryClass` — Class selector (exact, starts_with, ends_with, contains)
- `QueryAttribute` — Attribute selector (exact, starts_with, ends_with, contains)
- `LogicWrapper` — Logical AND/OR grouping for selectors

---

## Extending

You can add your own selector classes by implementing the `SelectorInterface`.

---

## License

MIT
