#Test task PHP Developer
##Задание
Нужно разработать веб-приложение для розыгрыша призов. После аутентификации пользователь может
нажать на кнопку и получить случайный приз. Призы бывают 3х типов: денежный (случайная сумма в
интервале), бонусные баллы (случайная сумма в интервале), физический предмет (случайный предмет из
списка).

Денежный приз может быть перечислен на счет пользователя в банке (HTTP запрос к API банка), баллы
зачислены на счет лояльности в приложении, предмет отправлен по почте (вручную работником).
Денежный приз может конвертироваться в баллы лояльности с учетом коэффициента. От приза можно
отказаться. Деньги и предметы ограничены, баллы лояльности нет.

###Комментарии к реализации:
- Необходимо предоставить прототип в PHP 7.4+ без использования фреймворков, но можно использовать любые
  библиотеки.
- Необходимо добавить консольную команду которая будет отправлять денежные призы на счета пользователей,
  которые еще не были отправлены пачками по N штук.
- Готовое задание требуется отправить ссылкой на репозиторий.
- Приложение должно разворачиваться в docker-окружении.


- Где хранить данные - mysql.
- В проекте обязательно соблюдение PSR
- Архитектура коммуникации между бек и фронт - REST
- Покрыть unit тестами проект не менее 30%
- Прогнать нагрузочное тестирование например: ab -k -c 1500 -n 10000 example.com/


##Инструкция по развертыванию проекта
1. Скопируйте .env.example в .env файл.
2. Запустите команду ```docker-compose build``` для сборки (если сборка уже была произведена, порпустить этот шаг)
3. Запустите команду ```docker-compose up -d```
4. Запустите команду ```docker exec -it slotegrator-php-1 bash``` для входа в контейнер php
5. Запустите команду ```./bin/doctrine orm:schema-tool:update --force``` для установки БД.
6. Запустите команду ```./bin/console.php app:fill-db``` для заполнения БД тестовыми данными.

Проект доступен по адресу http://localhost:8080/