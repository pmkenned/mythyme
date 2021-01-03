MyThyme

A scheduling tool

TODO:

- Allow password reset
- Handle events that span multiple days
- To-do sidebar
- Allow for modifying description, location, etc.
- Add hotkeys for creating, selecting, and moving events
- Handle overlapping events
- Create multiple views
- Allow for having origin date be any day of week

Lower priority TODO:

- Zoom animations
- Light/dark theme
- Offline mode
- Statistics
- End current event at now, begin next event now
- Import/export
- Uniquify colors
- Undo functionality
- '?' displays hot keys menu
- Use resize cursors when hovering over top and bottom
- Decide how to resize/move events that are very small
- Correctly handle responses from server, check for errors
- Allow for selecting multiple events
- Color events
- Desaturate past events
- Mitigate network latency
- Display original event as semi-transparent
- All-day events
- Handle right-click and double-click
- Each user should probably have their own table (database?)
- Mobile support
- Store user settings
- Click-drag zoom

Code improvements:

- Rectangle class
- Coordinate class
- Renderable object class
- View class (provides methods for coordinate -> date,time and vice versa)

DONE:

- Allow for modifying title
- Allow for dragging events across columns
- Handle case where session expires
- Display start and end times on event
- Indicate current date/time
- Allow user to select events
- Allow user to delete events
- Allow user to create events
- Create tables for testing purposes
- Retrieve events from database

Things to consider using:

- Font Awesome
- Google Fonts
- Base64 icons
- Bootstrap
- Less
- TypeScript
- React

Things to research:

- [Arrow functions vs regular functions](https://medium.com/swlh/javascript-arrow-functions-vs-regular-functions-5ec4a9076796)
- [Can POST data be JSON?](https://www.geeksforgeeks.org/how-to-receive-json-post-with-php/)
- CORS
- async/await

Resources:

- [png to base64](https://onlinepngtools.com/convert-png-to-base64)
- [Fetch API, Mozilla](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch)
- [Fetch API, JavaScript Tutorial](https://www.javascripttutorial.net/javascript-fetch-api/)
- [Apache 301 Redirect and preserving post data](https://stackoverflow.com/questions/13628831/apache-301-redirect-and-preserving-post-data)
- [async form posts](https://pqina.nl/blog/async-form-posts-with-a-couple-lines-of-vanilla-javascript/)
- [Backticks in SQL](https://chartio.com/learn/sql-tips/single-double-quote-and-backticks-in-mysql-queries/)
- [== vs ===](https://stackoverflow.com/questions/6003884/how-do-i-check-for-null-values-in-javascript)
- [Probably Don’t Base64 SVG](https://css-tricks.com/probably-dont-base64-svg/)
- [Pure CSS Slide-Down Animation](https://dzone.com/articles/pure-css-slide-down-animation-1)
