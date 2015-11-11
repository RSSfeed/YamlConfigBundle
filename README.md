# YamlConfigBundle
## Installation
```sh
composer require itf/yaml-config-bundle "dev-master"
php app/console assets:install
```
## Options
Set default file path (config.yml):
```yml
yaml_config:
    file_path: "%kernel.root_dir%/config/[config_file].yml"
```
Enable routing (routing.yml):
```yml
yaml_config:
    resource: "@YamlConfigBundle/Resources/config/routing.yml"
    prefix:   /_config
```
## License
MIT
