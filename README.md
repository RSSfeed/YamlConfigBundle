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
## Usage
Get service:
```php
$yml = $this->get('itf.confy');
```
Get all configurations from yml to php array:
```php
$yml = $this->get('itf.confy');
$array = $yml->getAll();

/* $array would look like this:
Array (
    [config] => some value
    [config2] => Array (
        [config2_1] => some other value
    )
)
*/
```
Get certain key:
```php
$yml = $this->get('itf.confy');
$value_of_config2_1 = $yml->get('config2.config2_1');
```
Update certain key:
```php
$yml = $this->get('itf.confy');
$yml->set('config2.config2_1', 'some new other value');
```
Save whole array to yml file:
```php
$array = /* ... */
$yml = $this->get('itf.confy');
$yml->saveArray($array);
```
Set other configuration file to process:
```php
$yml = $this->get('itf.confy');
$yml->setConfigFilePath('some/path/to/config.yml');
/* now get its content with: $yml->getAll(); */
```
## Web Interface
Access the web interface on ```http://[your-server]:8000/_config```
![Preview Image](http://i.imgur.com/W3DooAy.png)
## License
MIT
