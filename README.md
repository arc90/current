# current

Service which aggregates personal content streams into a single stream/page

## Resources and Pages

<table>
  <thead>
    <tr>
        <th>Name</th>
        <th>Path</th>
        <th>Representations</th>
        <th>Parameters</th>
    </tr>
  </thead>

  <tbody>
    <tr>
      <td>Home</td>
      <td><code>/</code></td>
      <td>HTML</td>
    </tr>

    <tr>
      <td>A User</td>
      <td><code>/{username}</code></td>
      <td>HTML</td>
    </tr>

    <tr>
      <td>A User’s Posts</td>
      <td><code>/{username}/posts</code></td>
      <td>JSON, RSS</td>
      <td><ul>
            <li><code>before-post-id</code>: optional, for paging</li>
            <li><code>max-count</code>: optional, defaults to `20`</li>
            <li><code>service-id</code>: optional, no default</li>
          </ul>
      </td>
    </tr>

    <tr>
      <td>A User’s Service</td>
      <td><code>/{username}/services/{service-id}</code></td>
      <td>JSON</td>
    </tr>

    <tr>
      <td>OAuth Handler</td>
      <td><code>/oauth-handler</code></td>
      <td>None (will respond with redirects only)</td>
    </tr>
  </tbody>
</table>

## Use Cases

### Creating an account

1. On the home page, the user will enter a username in a text field
2. The home page will send a HEAD request to the “a user” resource using the entered username
    1. e.g. if the user entered “kermit” then the HEAD request will be sent to `/kermit`
3. If the HEAD request returns 200, the username is taken, so the page will not allow the user
   to proceed until they try a different username. If it returns 404, the user may proceed.
4. The user chooses an initial service to add to their account. They click on the service name,
   navigating away from Current and to the first step of the OAuth flow for the chosen service.
    1. e.g. if they chose Twitter, they will be navigated to
       `https://api.twitter.com/oauth/authenticate`
5. The OAuth process will eventually redirect the user back to Current, to the url `/oauth-handler`;
6. The OAuth Handler resource will handle the result of the OAuth process and then redirect the user
   to their “a user” page, e.g. `/kermit`


### Viewing a user’s stream (with a browser)

1. The user will navigate to the resource “the user *x*”
    1. e.g. if the username is `kermit`, the path to this resource will be `/kermit`
    2. The server will send a GET request for this resource
        1. the request header Accept will prefer `text/html`
2. The server will return a 200 OK and a `text/html` representation of the resource
    1. The HTML document will contain no actual post content, just the page scaffold
    2. The HTML document will include some code
3. The browser will run the code specified by the HTML document
4. The code will send a HTTP GET request to the resource “the user *x*’s posts”
    1. e.g. `/kermit/posts`
    2. the request header Accept will prefer `application/json`
5. The server will return a 200 OK and a `application/json` representation of the resource


### Logging In
(coming soon)


### Adding a Service
(coming soon)


### Removing a Service
(coming soon)


## Authentication and Sessions
(coming soon)
