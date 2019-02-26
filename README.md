## Demo Github Oauth App.
Пример github oauth приложения.

Приложение получает access токен и выводит список всех доступных репозиториев на страницу.

## Установка
`composer install`

index.php
```
...
$config = [
    'settings' => [
        'scope'        => implode(' ', ['repo, user']), //Needed scopes
        'clientId'     => 'You app client id',
        'clientSecret' => 'You app client secret',
    ],
];
```

Homepage URL (github oauth app settings):

`http://localhost:8080`

Authorization callback URL

`http://localhost:8080/authorize`

### Запуск
`php -S localhost:8080`
