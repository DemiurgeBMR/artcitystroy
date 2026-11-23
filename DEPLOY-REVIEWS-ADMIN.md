# Развёртывание панели отзывов на Timeweb

Ветка: feature/reviews-admin. Сначала объедините её в main, затем на сервере выполните команды ниже.

## Обновление файлов

```bash
cd ~/artcitystroy/deploy_tmp && git pull origin main
cp index.html ~/artcitystroy/public_html/index.html
cp -r api admin includes uploads ~/artcitystroy/public_html/
mkdir -p ~/artcitystroy/private
```

Каталог private находится рядом с public_html, вне публичной веб-папки.

## Создание пароля

```bash
php ~/artcitystroy/deploy_tmp/scripts/setup-admin.php
```

Введите пароль длиной минимум 12 символов. Ввод скрыт. В GitHub сохраняется только скрипт, пароль и его хеш туда не попадают.

## Права доступа

```bash
chmod 750 ~/artcitystroy/private
chmod 600 ~/artcitystroy/private/admin.php
```

Открыть панель: https://ch086627.tw1.ru/admin/

Отзывы на главной странице загружаются через /api/reviews.php. В админ-панели доступны добавление, редактирование и удаление отзывов, добавление нескольких фотографий, удаление отдельных фотографий. Изображения до 8 МБ, JPG/PNG/WebP, не более 10 файлов за одну загрузку.
