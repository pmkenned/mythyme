# TODO

|Description                                                |Status  |Category|Hours|
|-----------------------------------------------------------|--------|--------|-----|
| Handle overlapping events                                 |Current |        |     |
| Render better on mobile/small screens                     |Current |        |     |
| Correctly handle responses from server, check for errors  |Progress|        | 1.5 |
| Light/dark theme                                          |Progress|        | 1.0 |
| '?' displays hot keys menu                                |Progress|        | 0.5 |
| Handle right-click and double-click                       |Progress|        | 2.0 |
| Mitigate network latency                                  |Progress|        | 2.0 |
| Color events                                              |Progress|        | 1.5 |
| Desaturate past events                                    |Progress|        | 0.5 |
| Click-drag zoom                                           |Progress|        | 2.0 |
| Stay logged in longer                                     |Progress|        | 4.0 |
| Handle events that span multiple days                     |TODO    |        | 2.0 |
| Recurring events                                          |TODO    |        | 6.0 |
| Automated testing                                         |TODO    |        | 5.0 |
| To-do sidebar                                             |TODO    |        | 4.0 |
| Allow for modifying description, location, etc.           |TODO    |        | 2.0 |
| Add hotkeys for creating, selecting, and moving events    |TODO    |        | 1.5 |
| Create multiple views                                     |TODO    |        | 4.0 |
| Allow for having origin date be any day of week           |TODO    |        | 2.0 |
| Undo functionality                                        |TODO    |        | 4.0 |
| Use resize cursors when hovering over top and bottom      |TODO    |        | 0.5 |
| Decide how to resize/move events that are very small      |TODO    |        | 1.0 |
| Easy copying of events (e.g. shift-click-drag)            |TODO    |        | 1.5 |
| Mobile support                                            |TODO    |        | 5.0 |
| Zoom animations                                           |TODO    |        | 1.5 |
| Offline mode                                              |TODO    |        | 6.0 |
| Statistics                                                |TODO    |        | 2.0 |
| End current event at now, begin next event now            |TODO    |        | 1.0 |
| Import/export                                             |TODO    |        | 2.0 |
| Uniquify colors                                           |TODO    |        | 1.0 |
| Allow for selecting multiple events                       |TODO    |        | 2.0 |
| Display original event as semi-transparent                |TODO    |        | 1.0 |
| All-day events                                            |TODO    |        | 2.0 |
| Store user settings                                       |TODO    |        | 3.0 |
| Scale text appropriately                                  |TODO    |Mobile  |     |
| Allow edits to existing events                            |TODO    |Mobile  |     |
| Allow creating new events                                 |TODO    |Mobile  |     |
| Handle gestures (swipe left and right, etc)               |TODO    |Mobile  |     |
| Rectangle class                                           |TODO    |Code    | 1.0 |
| Coordinate class                                          |TODO    |Code    | 1.0 |
| Renderable object class                                   |TODO    |Code    | 2.0 |
| View class (methods for coord <-> date,time)              |TODO    |Code    | 2.0 |
| Can select events that are obscured by days of week       |TODO    |Bug     | 0.2 |
| Text for short events spills outside the event            |TODO    |Bug     | 1.0 |
| Disable keydown/up listeners when entering name of event  |TODO    |Bug     | 0.2 |
| Each user should have their own table                     |DONE    |        |     |
| Allow password reset                                      |DONE    |        |     |
| Allow for modifying title                                 |DONE    |        |     |
| Allow for dragging events across columns                  |DONE    |        |     |
| Handle case where session expires                         |DONE    |        |     |
| Display start and end times on event                      |DONE    |        |     |
| Indicate current date/time                                |DONE    |        |     |
| Allow user to select events                               |DONE    |        |     |
| Allow user to delete events                               |DONE    |        |     |
| Allow user to create events                               |DONE    |        |     |
| Create tables for testing purposes                        |DONE    |        |     |
| Retrieve events from database                             |DONE    |        |     |

Things to consider using:

- Cookies
- Font Awesome
- Google Fonts
- Base64 icons
- Bootstrap
- Less, Sass
- TypeScript
- React

Things to research:

- [iCalendar](https://en.wikipedia.org/wiki/ICalendar)
- [Arrow functions vs regular functions](https://medium.com/swlh/javascript-arrow-functions-vs-regular-functions-5ec4a9076796)
- [Can POST data be JSON?](https://www.geeksforgeeks.org/how-to-receive-json-post-with-php/)
- CORS
- async/await

Resources:

- [How To Create A Forgotten Password System In PHP](https://www.youtube.com/watch?v=wUkKCMEYj9M)
- [PDO vs. MySQLi: The Battle of PHP Database APIs](https://websitebeaver.com/php-pdo-vs-mysqli)
- [png to base64](https://onlinepngtools.com/convert-png-to-base64)
- [Fetch API, Mozilla](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch)
- [Fetch API, JavaScript Tutorial](https://www.javascripttutorial.net/javascript-fetch-api/)
- [Apache 301 Redirect and preserving post data](https://stackoverflow.com/questions/13628831/apache-301-redirect-and-preserving-post-data)
- [async form posts](https://pqina.nl/blog/async-form-posts-with-a-couple-lines-of-vanilla-javascript/)
- [Backticks in SQL](https://chartio.com/learn/sql-tips/single-double-quote-and-backticks-in-mysql-queries/)
- [== vs ===](https://stackoverflow.com/questions/6003884/how-do-i-check-for-null-values-in-javascript)
- [Probably Don’t Base64 SVG](https://css-tricks.com/probably-dont-base64-svg/)
- [Pure CSS Slide-Down Animation](https://dzone.com/articles/pure-css-slide-down-animation-1)
- [Responsive Design](https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Responsive_Design)
- [Media queries](https://www.emailonacid.com/blog/article/email-development/emailology_media_queries_demystified_min-width_and_max-width/)
- [The Async Await Episode I Promised](https://www.youtube.com/watch?v=vn3tm0quoqE)
- [Asynchronous Vs Synchronous Programming](https://www.youtube.com/watch?v=Kpn2ajSa92c)
- [JavaScript Async Await](https://www.youtube.com/watch?v=V_Kr9OSfDeU)
- [Async Javascript Tutorial For Beginners (Callbacks, Promises, Async Await).](https://www.youtube.com/watch?v=_8gHHBlbziw)
- [PHP session expiring after 24 minutes](https://www.reddit.com/r/PHP/comments/zko6e/php_session_expiring_after_24_minutes/)
- [How do I expire a PHP session after 30 minutes?](https://stackoverflow.com/questions/520237/how-do-i-expire-a-php-session-after-30-minutes/1270960#1270960)
