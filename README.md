# Mihdan: Clockwork

![](./assets/screenshot-1.png)

Отладка WordPress при помощи [Clockwork](https://underground.works/clockwork/)

## Как использовать

1. Ставите расширение Clockwork под [Chrome](https://chrome.google.com/webstore/detail/clockwork/dmggabnehkmmfmdffgajcflpdjlnoemp) или [Firefox](https://addons.mozilla.org/en-US/firefox/addon/clockwork-dev-tools/).
2. Ставите плагин **Mihdan: Clockwork**
3. Открываете в панели Dev-Tools вкладку Clockwork
4. В вашей теме пишем `do_action( 'mc_notice', 'Усё пропало, шеф!', array( 1, 2 ) )`
5. Результат наблюдаем в консоли браузера

![](./assets/screenshot-2.png)

## Типы оповещений

1. `do_action( 'mc_emergency', 'Усё пропало, шеф!', array( 1, 2 ) )`
2. `do_action( 'mc_alert', 'Усё пропало, шеф!', array( 1, 2 ) )`
3. `do_action( 'mc_critical', 'Усё пропало, шеф!', array( 1, 2 ) )`
4. `do_action( 'mc_error', 'Усё пропало, шеф!', array( 1, 2 ) )`
5. `do_action( 'mc_warning', 'Усё пропало, шеф!', array( 1, 2 ) )`
6. `do_action( 'mc_notice', 'Усё пропало, шеф!', array( 1, 2 ) )`
7. `do_action( 'mc_info', 'Усё пропало, шеф!', array( 1, 2 ) )`
8. `do_action( 'mc_debug', 'Усё пропало, шеф!', array( 1, 2 ) )`