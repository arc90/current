# current

Application which aggregates personal content streams into a single stream/page

## Architectural Style

* Current is a web application which uses the REST architectural style

## Representations

* This application uses both HTML and JSON for its representations
* Some resources are available as HTML, some as JSON, and some as both
* JSON representations use [DocJSON](https://github.com/docjson/docjson) to embed hypermedia in the
  representations
    * As of this writing on 19 August 2013, DocJSON is a draft specification; this application uses
      [this version](https://github.com/docjson/docjson/blob/ba3ab2f108fc237ae389e4acf478c20cd63fa8b9/README.md)
      of the draft

## Resources and Pages

### Summary

<table>
  <thead>
    <tr>
        <th>Name</th>
        <th>Path</th>
        <th>Representations</th>
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
      <td>HTML, JSON</td>
    </tr>

    <tr>
      <td>A User’s Posts</td>
      <td><code>/{username}/posts</code></td>
      <td>JSON, RSS</td>
    </tr>

    <tr>
      <td>A User’s Service</td>
      <td><code>/{username}/services/{service-id}</code></td>
      <td>none (will respond with 204 or errors)</td>
    </tr>

    <tr>
      <td>OAuth Handler</td>
      <td><code>/oauth-handler</code></td>
      <td>none (will respond with redirects only)</td>
    </tr>
  </tbody>
</table>

### Home

### A User

#### HTML Representation

Designed to be rendered by and viewed in a browser, this page will depend heavily on JavaScript to
retrieve and render its content.

The representation will contain the scaffolding for displaying a user’s stream of posts; JavaScript
code will retrieve and render the actual post stream.

#### JSON Representation

##### Example

```json
{
  "username": "kermit",
  "display_name": "Kermit the Frog",
  "gravatar_id": "",
  "posts": {
    "_type": "form",
    "method": "GET"
    "href": "https://current/kermit/posts"
    "fields": [
      {"name": "service"},
      {"name": "before-post"},
      {"name": "max-count"}
    ]
  },
  "services": {
    "_type": "list",
    "items": [
      {
        "name": "github"
        "_type": "link",
        "href": "https://current/kermit/services/github",
        "posts": {
          "_type": "link",
          "href": "https://current/kermit/posts?service=github"
        }
      },
      {
        "name": "flickr"
        "_type": "link",
        "href": "https://current/kermit/services/flickr",
        "posts": {
          "_type": "link",
          "href": "https://current/kermit/posts?service=flickr"
        }
      }
    ]
  }
}
```

### A User’s Posts

#### Parameters
* `before-post` optional string; for “paging” (technically cursoring)
* `max-count` optional integer; min: `1`; max: `100`; defaults to `20`
* `service` optional string; no default

#### JSON Representation

Each post has these properties:

<dl>
  <dt>href</dt>
  <dd>the URL of the post</dd>
  
  <dt>service</dt>
  <dd>The name of the service; supported values are: <code>github</code>, <code>twitter</code>,
      <code>flickr</code>, <code>tumblr</code>, etc. (canonical list coming soon)</dd>

  <dt>subject</dt>
  <dd>The subject of the post; supported values are: issue, commit, post, message</dd>

  <dt>action</dt>
  <dd>What the user did to/with the subject in order to create the post; supported values are:
      <code>created</code>, etc. (TBD soon)</dd>

  <dt>body</dt>
  <dd>The main content of the post, formatted as HTML</dd>
</dl>

##### Example

```json
{
  "posts": {
    "_type": "list",
    "items": [
      {
        "_type": "link",
        "href": "https://github.com/crsmithdev/arrow/issues/31"
        "id": "234234556"
        "service": "github",
        "subject": "issue",
        "action": "created",
        "title": "The docs don’t make clear how to parse an ISO-8601 datetime string",
        "subtitle": null,
        "body": "<p>I’ve been perusing the docs for 10 minutes now trying to figure this out. I’d think it’s a common-enough use case that it should have an example in the docs.</p><p>It seems I should use <code>arrow.get(datetime_string, pattern_string)</code> but I don’t know what syntax to use for the pattern, and the docs don’t say what syntax is used so I can’t look it up.</p><p>So, some suggestions:<ol><li>add an example of parsing an ISO-8601 datetime string (with an offset)</li><li> add a link to the docs for the pattern syntax</li><li>add a module containing some “constants” with common datetime pattern strings.</li></ol></p>"
      }
    ],
    "next": "https://current/kermit/posts?before-post-id=234234556"
  }
}
```

### A User’s Service


### OAuth Handler


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
3. The browser will run the JS code specified by the HTML document
4. The JS code will send an asynchronous HTTP GET request to the resource “the user *x*”
    1. e.g. `/kermit`
    2. the request header Accept will prefer `application/json`
    3. When the server responds to the request with a `200 OK` and
       an `application/json` representation of the resource, the JS code will parse
       the response body and render the list of services contained therein in the page
5. The JS code will send an asynchronous HTTP GET request to the resource “the user *x*’s posts”
    1. e.g. `/kermit/posts`
    2. the request header Accept will prefer `application/json`
    3. When the server responds to the request with a `200 OK` and
       an `application/json` representation of the resource, the JS code will parse
       the response body and render the list of posts contained therein in the page


### Logging In
(coming soon)


### Adding a Service
(coming soon)


### Removing a Service
(coming soon)


## Authentication and Sessions
(coming soon)


## Errors
(coming soon)
