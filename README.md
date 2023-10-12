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
```json
{
    "success": true,
    "user": {
        "id": 1,
        "apiToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2OTY4NTcwNTAsImV4cCI6MTY5Njg2MDY1MCwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidXNlckBleGFtcGxlLmNvbSJ9.QYWA4fEVF9Z3cy3YoAyyvoRWZeTE9m5fEAAZqORwi_g-igviEWlWapTWZagZgzYEDLLlW1GouyFELuc9YhuSiFS_xJNAHEKQOZYVPaOPEpWQjyJZC_Hf-OB4alPMckCVnQych1TDssJF2DKGF9rM-AGEaTtHQmcx8rp7f-kEr7GIdnWJKx7ROwLARMsjHn8tZh7x5C7yxE165aYbUPKO7hJP3xPUe2JXrbEbMUuWPWaWV5NLwKo00rTnh2KUSKsAmeFWNrKDzjvEZ2MLalzGcA03slZMAy-1zH4CNPphhQ9e7pIEBYR-hZPEvNL6BrMmX3rJur5y-qU_7fohKGHrxA"
    }
}
```

получение курсов валют
```
HEADERS
    id     | 1
    token  | eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2OTY3NzU0MDksImV4cCI6MTY5Njc3OTAwOSwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidXNlckBleGFtcGxlLmNvbSJ9.W-W_xy1J0iCxfnwXIen4g7RsZC5AhxcmCoDseAsNPbFdMohg0DaeBe-E0F3JbVXsF3Q6H0-d8huZxA-ShDMdIm8Q-Wp8C6QXjFbCMIJdBgajQsel_p05qGFoPntBkYMfdccS7jTGM6CY8VhsSOP99FWox7_70cutMAtD_XkXyYU4hwd5MTBUJaI1By8TfM4MOZvWe85ULP4WLN__Kzcasgt13EridWTUe4QcDaRE4k_SA4bwJl7udH5mh3MoiRKzAJvUe0O_vCzucUBeR1Qls_rhOvz9Xop0rvDSsylvibfMSVfD5lyFoGhkI3BSvUbAxD96UjsD5jf450rnK4vw_g

GET test-cbr.localhost/api/getCurrency

{
    "date_start": "21.09.2023",
    "date_end": "09.10.2023"
}
```

ответ
```json
{
  "success": true,
  "currencies": {
    "USD": {
      "21.09.2023": "96,6172",
      "22.09.2023": "96,0762",
      "23.09.2023": "96,0419",
      "24.09.2023": "96,0419",
      "25.09.2023": "96,0419",
      "26.09.2023": "96,1456",
      "27.09.2023": "96,2378",
      "28.09.2023": "96,5000",
      "29.09.2023": "97,0018",
      "30.09.2023": "97,4147",
      "01.10.2023": "97,4147",
      "02.10.2023": "97,4147",
      "03.10.2023": "98,4785",
      "04.10.2023": "99,2677",
      "05.10.2023": "99,4555",
      "06.10.2023": "99,6762",
      "07.10.2023": "100,4911",
      "08.10.2023": "100,4911",
      "09.10.2023": "100,4911"
    },
    "EUR": {
      "21.09.2023": "103,3699",
      "22.09.2023": "102,3606",
      "23.09.2023": "102,2485",
      "24.09.2023": "102,2485",
      "25.09.2023": "102,2485",
      "26.09.2023": "102,2453",
      "27.09.2023": "101,9888",
      "28.09.2023": "101,9780",
      "29.09.2023": "102,0979",
      "30.09.2023": "103,1631",
      "01.10.2023": "103,1631",
      "02.10.2023": "103,1631",
      "03.10.2023": "103,8680",
      "04.10.2023": "104,0621",
      "05.10.2023": "104,3024",
      "06.10.2023": "104,7877",
      "07.10.2023": "106,0100",
      "08.10.2023": "106,0100",
      "09.10.2023": "106,0100"
    },
    "CNY": {
      "21.09.2023": "13,2097",
      "22.09.2023": "13,1335",
      "23.09.2023": "13,1414",
      "24.09.2023": "13,1414",
      "25.09.2023": "13,1414",
      "26.09.2023": "13,1394",
      "27.09.2023": "13,1504",
      "28.09.2023": "13,1852",
      "29.09.2023": "13,2753",
      "30.09.2023": "13,3587",
      "01.10.2023": "13,3587",
      "02.10.2023": "13,3587",
      "03.10.2023": "13,4955",
      "04.10.2023": "13,5556",
      "05.10.2023": "13,5779",
      "06.10.2023": "13,6183",
      "07.10.2023": "13,7373",
      "08.10.2023": "13,7373",
      "09.10.2023": "13,7373"
    }
  }
}
```
