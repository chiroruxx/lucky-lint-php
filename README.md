# lucky-lint

lucky-lint は命名が良いかを（画数占いで）調べます。

## Installation
### Manual
コードをダウンロードし、プロジェクトの phpcs.xml で以下のように設定してください。

```xml
<config name="installed_paths" value="/path/to/lint/code"/>
<rule ref="LuckyLint.Naming.LuckyLint" />
```
