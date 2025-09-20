<div align="center">

[![Banner of the github account](./resources/assets/images/github-visual.png)](https://memoria.alexis-gousseau.com/)

[![Website test](https://img.shields.io/website-up-down-green-red/https/memoria.alexis-gousseau.com?style=for-the-badge)](https://memoria.alexis-gousseau.com)
[![GitHub latest commit](https://img.shields.io/github/last-commit/alexis-gss/memoria/develop?color=5A718A&style=for-the-badge)](https://github.com/alexis-gss/memoria/commit/master)
[![GitHub tag](https://img.shields.io/github/tag/alexis-gss/memoria?style=for-the-badge&color=5A718A)](https://github.com/alexis-gss/memoria/tags)
[![GitHub License](https://img.shields.io/github/license/alexis-gss/memoria?color=5A718A&style=for-the-badge)](https://github.com/alexis-gss/memoria/blob/master/LICENSE)

</div>

# Introduction
[Memoria](http://memoria.alexis-gousseau.com/) is, as its name suggests, a gallery of images from a multitude of video games I've played. Each game is listed and associated with a folder to sort them by license or universe.

Each element (name, image, folder, color, etc.) can be managed in the back-office (/bo) and accessed via authentication.

# Table of contents

- [Introduction](#introduction)
- [Table of contents](#table-of-contents)
- [Frameworks, Platforms and Libraries](#frameworks-platforms-and-libraries)
- [Documentation](#documentation)
- [Contributing](#contributing)
    - [Create a task](#create-a-task)
    - [Fixing a Bug](#fixing-a-bug)
    - [Proposing a Change](#proposing-a-change)
- [Changelog](#changelog)
- [Copyright and License](#copyright-and-license)

# Frameworks, Platforms and Libraries
[![SASS](https://img.shields.io/badge/SASS-hotpink.svg?style=for-the-badge&logo=SASS&logoColor=white)](https://sass-lang.com/)
[![TypeScript](https://img.shields.io/badge/typescript-%23007ACC.svg?style=for-the-badge&logo=typescript&logoColor=white)](https://www.typescriptlang.org/)
[![Vue.js](https://img.shields.io/badge/vuejs-%2335495e.svg?style=for-the-badge&logo=vuedotjs&logoColor=%234FC08D)](https://vuejs.org/)
[![Bootstrap](https://img.shields.io/badge/bootstrap-%23563D7C.svg?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/docs/master)

# Documentation

You can find all the documentation of this project on this link : [Memoria documentation](https://doc-memoria.alexis-gousseau.com).

# Contributing

### Create a task

You can create a [new issue](https://github.com/alexis-gss/memoria/issues/new/choose) with a specific templates : bug or feature.

Once your code is working, please run the following commands `npm run stylelint`, `npm run eslint`, `./vendor/bin/phpstan`, `./vendor/bin/phpcs` and check tests `php artisan test` to verify that your code is following the same coding standards (in all cases, there is github actions that check this part).

### Fixing a Bug

When fixing a bug please make sure to test it in several browsers. If you are not able to do so, mention that in a PR comment, so other contributors can do it.

### Proposing a Change

When implementing a feature please create an issue first explaining your idea and asking whether there's need for such a feature. Remember the script's core philosophy is to stay simple and minimal, doing one thing and doing it right.

# Changelog

Latest version v5.3.2.

See the [CHANGELOG.md](CHANGELOG.md) file for details.

# Copyright and License

[Memoria](http://memoria.alexis-gousseau.com/) was written by [Alexis Gousseau](https://github.com/alexis-gss).

Copyright (c) 2022 and beyond Alexis Gousseau.
