#### Выпусной проект №4

Используя спроектированную базу данных из ДЗ №7 разработайте проект на Laravel (или Yii) функционал интернет-каталога.

- Пользователь:
    - Может зарегистрироваться
    - Может просматривать список категорий, список товаров, страницу одного товара
    - По нажатию кнопки купить, со страницы одного товара, пользователю предлагается связаться с менеджером и просят оставить Имя и email. E-mail подставляется в форму из данных об авторизованном пользователе. Имя,  E-mail и id товара записывается в базу. Желательно реализовать это с помощью javascript и поп-ап окна.

- Товар имеет следующие характеристики:
    - Название
    - Категория
    - Цена
    - Фотография
    - Описание

- Категория имеет следующие характеристики:
    - Название
    - Описание

- Заказы:
    - Пользователь может оставить заявку на покупку товара. id товара и email пользователя записывается.
    - Администратор получает уведомление на E-mail через SMTP.

- Администратор:
    - Может создавать\редактирование\удалять категории
    - Может создавать\редактировать\удалять товары
    - Может просматривать заказы
    - Может установить\изменить адрес для получения уведомлений.
    - Права администратора можно получить поставив галочку при регистрации :)
