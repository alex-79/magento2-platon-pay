# Magento2-модуль прийому платежів для PSP Platon

* Сайт сервісу - https://platon.ua/ua
* Оригінальний модуль - https://github.com/platonua/Magento_2.0-2.3_Ukraine
* Інструкція з налаштування - https://platon.ua/ua/for-shops/magento 

Зміни відносно оригінального модуля:
* запис взаємодії Platon та Magento 2 в файл *var/log/platon_callback.log* (запис саме в цей файл прямо вказаний в інструкції з налаштування модуля, але не реалізований)
* виправлено bug, який не дозволяє коректно закінчити процес замовлення:  *PHP message: PHP Warning: Uncaught Exception: Warning: var_export does not handle circular references in Controller/Process/Index.php on line 174*
* виправлена робота з налаштуваннями в адмінці
* відмова від прямого використання *ObjectManager* (https://magento.stackexchange.com/a/75204/37969)

## Встановлення:

```
composer require alex79/platon-pay:dev-master
php bin/magento setup:upgrade
```

## Посилання для колбеків:

https://ВАШ_САЙТ/platon_platon_pay/process/index

## Тестові дані:

Для тестування використовуйте наступні реквізити

| Номер карти  | Місяць / Рік | CVV2 | Результат |
| :---:  | :---:  | :---:  | --- |
| 4111  1111  1111  1111 | 01 / 2022 | Довільні три цифри | Успішна оплата без 3DS перевірки |
| 4111  1111  1111  1111 | 02 / 2022 | Довільні три цифри | Не успішна оплата без 3DS перевірки |
| 4111  1111  1111  1111 | 05 / 2022 | Довільні три цифри | Успішна оплата з 3DS перевіркою |
| 4111  1111  1111  1111 | 06 / 2022 | Довільні три цифри | Не успішна оплата з 3DS перевіркою |
