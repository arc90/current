<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>

    <!-- Basic Page Needs
  ================================================== -->
    <meta charset="utf-8">
    <title>Test</title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Mobile Specific Metas
  ================================================== -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- CSS
  ================================================== -->
    <link rel="stylesheet" href="styles.css">
    <link href='http://fonts.googleapis.com/css?family=Euphoria+Script' rel='stylesheet' type='text/css'>

    <!-- Javascript
  ================================================== -->
    <script src="js/modernizr.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="js/handlebars.js"></script>
    <script src="js/timeago.js"></script>
    <script src="js/feed.js"></script>

</head>
<body id="top">

    <header class="header">
        <div class="l-wrapper">
            <h2>Current</h2>
            <nav class="nav-primary"></nav>
        </div>
    </header>

    <section class="main l-wrapper">
        <header class="profile">
            <img src="http://www.gravatar.com/avatar/9a914c352d22c9a7c9c59b46b337d193?d=mm&s=128" class="profile__avatar"/>
            <h2 class="profile__name">Jim Nielsen</h2>
            <p class="profile__bio">This is some kind of an optional bio. Do we even want this for 1.0? Probably we do.</p>
        </header>
        <div class="content">
            <nav class="nav-secondary">
                <a href="#top" class="nav-top icon icon-arrow-up" title="Return to top"></a>
            </nav>
            <ul id="feed" class="feed">
                <li class="feed-item no-js">Sorry, you need javascript enabled to view this page.</li>
            </ul>
        </div>
    </section>

    

    <script id="template-nav-secondary" type="text/x-handlebars-template">
        <ul class="nav-secondary__list">
            <li class="is-active"><a href="#" id="all" class="icon icon-menu"><span>All Feeds</span></a></li>
            {{#each services}}
                <li>
                    <a href="#" id="{{this}}" class="icon icon-{{this}}" title="{{this}}"><span>{{this}}</span></a></li>
                </li>
            {{/each}}
        </ul>
        <select class="nav-secondary__select">
            <option value="all" selected="selected">All Feeds</option>
            <optgroup label="Individual Feeds">
                {{#each services}}
                    <option value="{{this}}">{{this}}</option>
                {{/each}}
            </optgroup>
        </select>
    </script>

    <script id="template-feed" type="text/x-handlebars-template">
        {{#entries}}
            <li class="feed__item {{id}} icon-{{id}}">
                
                <time class="timestamp"><span class="icon icon-clock"></span> {{format_date date}}</time>

                {{#equal id 'readability'}}
                    <h2 class="feed__item__title"><a href="{{link}}">{{title}}</a></h2>
                    <p class="feed__item__text">{{{trim_excerpt excerpt}}}&hellip;</p>
                {{/equal}}

                {{#equal id 'dribbble'}}
                    <a href="{{link}}" class="feed__item__image"><img src="{{image_url}}" /></a>
                {{/equal}}

                {{#equal id 'instagram'}}
                    <a href="{{link}}" class="feed__item__image"><img src="{{image_url}}" /></a>
                {{/equal}}

                {{#equal id 'scriptogram'}}
                    <h2 class="feed__item__title"><a href="{{link}}">{{title}}</a></h2>
                {{/equal}}

                {{#equal id 'yelp'}}
                    <h2 class="feed__item__title"><a href="{{link}}">{{title}}</a></h2>
                    <p class="feed__item__text">{{{excerpt}}}</p>
                {{/equal}}

                {{#equal id 'github'}}
                    <div class="feed__item__text">{{{content}}}</div>
                {{/equal}}

                {{#equal id 'twitter'}}
                    <p class="feed__item__text">{{{parse_twitter content}}}</p>
                {{/equal}}
                
            </li>
        {{/entries}}
    </script>

</body>
</html>