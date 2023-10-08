<h1>CBR TEST</h1>

<h2>Развертывание сервиса</h2>

устанавливаем:
- nginx
- postgresql
- php8.2

пример конфига nginx
```
server {
        listen 80;
        listen [::]:80;

        root /home/user/test-cbr/public;

        index index.html index.htm index.nginx-debian.html index.php;

        server_name test-cbr.localhost;

        location / {
                try_files $uri /index.php$is_args$args;
        }

        location ~ ^/index\.php(/|$) {
                fastcgi_pass unix:/run/php/php8.2-fpm.sock;
                fastcgi_split_path_info ^(.+\.php)(/.*)$;
                include fastcgi_params;

                fastcgi_buffer_size 128k;
                fastcgi_buffers 4 256k;
                fastcgi_busy_buffers_size 256k;

                fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
                fastcgi_param DOCUMENT_ROOT $realpath_root;

                internal;
        }

        location ~ \.php$ {
                return 404;
        }
}

```

создаем базу данных, пользователя, выдаем права
```sql
CREATE DATABASE test_cbr;
create user cbr with encrypted password 'securePassword';
grant all privileges on database test_cbr to cbr;
```

скачиваем проект
`git clone https://github.com/RabbitTheGrey/test-cbr.git`

создаем в корне файл `.env`, переносим в него содержимое `.env.dist`

далее бахаем команды
```shell
composer install
bin/console lexik:jwt:generate-keypair #генерация связки ключей для создания токенов
bin/console doc:mig:mig #миграции
bin/console doctrine:fixtures:load #фикстура с тестовым пользователем
```

<h2>Пример использования</h2>

авторизация
```
POST test-cbr.localhost/api/login

{
    "email": "user@example.com",
    "password": "securePassword"
}
```

ответ
```
{
    "success": true,
    "user_id": 1,
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2OTY3NzU0MDksImV4cCI6MTY5Njc3OTAwOSwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidXNlckBleGFtcGxlLmNvbSJ9.W-W_xy1J0iCxfnwXIen4g7RsZC5AhxcmCoDseAsNPbFdMohg0DaeBe-E0F3JbVXsF3Q6H0-d8huZxA-ShDMdIm8Q-Wp8C6QXjFbCMIJdBgajQsel_p05qGFoPntBkYMfdccS7jTGM6CY8VhsSOP99FWox7_70cutMAtD_XkXyYU4hwd5MTBUJaI1By8TfM4MOZvWe85ULP4WLN__Kzcasgt13EridWTUe4QcDaRE4k_SA4bwJl7udH5mh3MoiRKzAJvUe0O_vCzucUBeR1Qls_rhOvz9Xop0rvDSsylvibfMSVfD5lyFoGhkI3BSvUbAxD96UjsD5jf450rnK4vw_g"
}
```

получение курсов валют
```
HEADERS
    id     | 1
    token  | eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2OTY3NzU0MDksImV4cCI6MTY5Njc3OTAwOSwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidXNlckBleGFtcGxlLmNvbSJ9.W-W_xy1J0iCxfnwXIen4g7RsZC5AhxcmCoDseAsNPbFdMohg0DaeBe-E0F3JbVXsF3Q6H0-d8huZxA-ShDMdIm8Q-Wp8C6QXjFbCMIJdBgajQsel_p05qGFoPntBkYMfdccS7jTGM6CY8VhsSOP99FWox7_70cutMAtD_XkXyYU4hwd5MTBUJaI1By8TfM4MOZvWe85ULP4WLN__Kzcasgt13EridWTUe4QcDaRE4k_SA4bwJl7udH5mh3MoiRKzAJvUe0O_vCzucUBeR1Qls_rhOvz9Xop0rvDSsylvibfMSVfD5lyFoGhkI3BSvUbAxD96UjsD5jf450rnK4vw_g

GET test-cbr.localhost/api/getCurrency

{
    "date_start": "20.08.2023",
    "date_end": "22.08.2023"
}
```
