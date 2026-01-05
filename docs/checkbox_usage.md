### Checkbox Extractor

---
🧪 Example usage
```php
use App\Support\Markdown\CheckboxExtractor;

$markdown = <<<MD
Things to do:
- [ ] Reset GitHub connector
- [x] Decide architecture
- [ ] Implement WS protocol
MD;

$checkboxes = CheckboxExtractor::extract($markdown);
```

Result:
``` php
[
  ['index' => 0, 'label' => 'Reset GitHub connector', 'checked' => false],
  ['index' => 1, 'label' => 'Decide architecture', 'checked' => true],
  ['index' => 2, 'label' => 'Implement WS protocol', 'checked' => false],
]
```
---

### 🔐 Why this extractor is intentionally simple

- No AST parsing yet (you don’t need it)

- Line-based = predictable

- Regex is anchored to line start → safe

- Ignores nested lists (for now — good thing)
