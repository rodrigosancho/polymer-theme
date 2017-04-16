# Polymer SPA Theme for Wordpress

This theme aims to be a starting point to develop a Polymer SPA applications using Wordpress to feed it. It's based on the Polymer's team `Polymer starting Kit`.


It is still in development stage and some bugs may appear.

## Overview
A demo can be found in [wp.trofrigo.me](http://wp.trofrigo.me)
## Technology
In order to work, this [WP REST API](http://v2.wp-api.org/), must be installed for a Wordpress version lower than 4.7.3.
To increase the speed and improve the user experience, [Varnish](https://varnish-cache.org/) can be installed.


## Installation
* Clone this rep; `git clone https://github.com/trofrigo/polymer-theme.git`
* Install the theme's bower dependencies. Execute `bower install` in the root of this project. In order to this,
[Bower](https://bower.io/#install-bower) must be installed.
* Install and activate this theme into your [Wordpress](https://wordpress.org) installation.
* Logged into Wordpress's backoffice, go to Settings>Permalinks, Select Custom Structure and complete it with
`/blog/%postname%/`. This will concat `blog` to every post url between the blog's domain and the slug, allowing to
the shell to tell between a post and a page.
* Create a page called Blog. This is needed in order to be able to edit the blog's title and description.

## File structure
The whole Polymer's application can be found in `src/`. There you will find three folders `core/`, `shared/` and `templates/`.

The main idea is that all the representational elements shared between templates, should be placed into the `shared/` folder.

All the templates must be placed into the `templates/` folder. With this theme you will find:
* `template-404.html`
* `template-blog.html`
* `template-blog-detail.html`
* `template-page-detail.html`

Theme's core is placed into the `core/` folder. There you will find the shell as well as other non represetational
elements that can be placed into the templates, as for example:
* `polymer-theme-service.html` - Element with all the logic needed to connect with the Wordpress`s API
* `polymer-theme-shell.html` - That's the actual core of this theme. This element contains the application's router and
decides which template must be display.

## Features
### Template hierarchy Wordpress like
This theme tries to mimic the Wordpress template behaviour. In every route change, theme's router will try to determinate
what the `taxonomy` and the `slug` of the next view is. Once those params are determinated, the shell will try to find a
template for the view. First of all will look for a template like `templates/template-{slug}.html`,
if this template does not exist, will resolve into `templates/template-{taxonomy}-detail.html`.

This hierarchy allows the user to set a default view for a taxonomy detail (typically `post-detail` and `page-detail`)
and also overwrite it for any special post, just creating a template called `template-{slug}.html` with the post slug.