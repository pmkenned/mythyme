# Notes

## Things to consider using:

- Cookies
- Font Awesome
- Google Fonts
- Base64 icons
- Bootstrap
- Less, Sass
- TypeScript
- React

## Things to research:

- [iCalendar](https://en.wikipedia.org/wiki/ICalendar)
- [Arrow functions vs regular functions](https://medium.com/swlh/javascript-arrow-functions-vs-regular-functions-5ec4a9076796)
- [Can POST data be JSON?](https://www.geeksforgeeks.org/how-to-receive-json-post-with-php/)
- CORS
- async/await

## Resources:

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

## Thoughts

The current view is an object that should be passed unhandled events such as clicks,
keypresses and such.

Goal:

- Create an online scheduling tool similar to Google calendar but which the ability to add any specific features I want
  - Emphasis on keyboard shortcuts for quickly rescheduling and modifying events; vim-like
  - Easy to schedule and visualize many short events
  - Features for managing recurring tasks and ongoing projects
  - Fast, responsive, lightweight
- How can I implement it?
  - DOM
  - SVG
  - HTML canvas
- requestAnimationFrame
  - Performance issues
  - Mostly not changing
  - Do I only render when state changes occur?
    - Keeping track of this seems bug-prone
    - Doing a full-render at 60fps seems wasteful

## Code Notes

```
let timer_num = 0;

function test() {
    foo = {s: "hi", t: 4};
    bar = {s: "there", t: 5};
    baz = {s: "you", t: 3};
    console.log('%c Hello, world', 'color: orange; font-weight: bold;');
    console.table([foo, bar, baz], ['s','t']);
    console.dir(foo);
    console.dir(checkForUpdates);
    console.trace('my trace');

    console.groupCollapsed();
        console.warn('this is a warning');
        console.info('this is info');
        console.error('this is an error');
        console.assert(1==2, '1 doesn\'t equal two');
    console.groupEnd()
    //console.clear();
    console.count();

    const timer_str = `${timer_num}`;
    timer_num++;
    console.time(timer_str);
    fetch('mt_functions.php?func=checkForUpdates')
      .then(response => response.json())
      .then(data => {console.log(data); console.timeEnd(timer_str); });

    return 'end of test';
}

// TODO: implement interface testing
function test() {
    const mdown = new CustomEvent('mousedown');
    mdown.button  = LEFT_MOUSE_BUTTON;
    mdown.clientX = 300;
    mdown.clientY = 300;
    const mup = new CustomEvent('mouseup');
    mup.button  = LEFT_MOUSE_BUTTON;
    mup.clientX = 300;
    mup.clientY = 300;
    const my_element = document.getElementById('myCanvas');
    window.setTimeout(() => {window.dispatchEvent(mdown); console.log('mdown');}, 500);
    window.setTimeout(() => {window.dispatchEvent(mup); console.log('mup');}, 500);
}

```
