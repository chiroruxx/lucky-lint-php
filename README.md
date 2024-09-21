# lucky-lint

lucky-lint は命名が良いかを（画数占いで）調べます。

## Installation
### Composer
```sh
composer require --dev "chiroruxxxx/lucky-lint:dev-master"
```

を実施後に、プロジェクトの phpcs.xml で以下のように設定してください。

```xml
<config name="installed_paths" value="vendor/chiroruxxxx/lucky-lint"/>
<rule ref="LuckyLint" />
```

### Manual
コードをダウンロードし、プロジェクトの phpcs.xml で以下のように設定してください。
```xml
<config name="installed_paths" value="/path/to/lucky-lint"/>
<rule ref="LuckyLint" />
```

## Usage
`minLevel` を指定することで、エラー出力する対象のレベルを設定することができます。
`minLevel` は 1~3 で指定できます。

```xml
<rule ref="LuckyLint.Naming.LuckyLint">
    <properties>
        <property name="minLevel" value="3"/>
    </properties>
</rule>
```
