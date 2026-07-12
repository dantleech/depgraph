Composer PHP Dependency Graph Generator
=======================================

[![CI](https://github.com/dantleech/depgraph/actions/workflows/ci.yml/badge.svg)](https://github.com/dantleech/depgraph/actions/workflows/ci.yml)

Generates rich dependency diagrams for your composer-based PHP project.

<img width="15994" height="2429" alt="Image" src="https://github.com/user-attachments/assets/fc1a43dd-2153-46c9-8d88-64b2f70dbe93" />

Above you can see:

- Dotted lines indicate a dev dependency.
- Bold lines indicate a direct dependency.
- Package version constraints listed on the lines.
- Licence information
- Red nodes indicate a package with security issues.
- Orange nodes are abandonned.
- Yellow nodes are outdated.
  - `@1.0.0` indicates the installed version.
  - `=1.0.0` indicates the latest version.
- The distance from the root package is shown as `+[0-n]`

<img width="486" height="328" alt="Image" src="https://github.com/user-attachments/assets/649f1696-5415-4bb2-998d-976513a6e61f" />

Installation
------------

You could install it as follows:

```
git clone git@github.com:dantleech/depgraph
cd depgraph
composer install
ln -s $(pwd)/bin/depgraph $HOME/.local/bin/depgraph
```

Features
--------

- Limit to production or dependency packages (`--no-dev` `--no-prod`).
- Limit depth of graph (`--max-depth=2`)
- Highlight packages with CVEs (`--audit`)
- Highlight outdated packages (`--outdated`)

Usage
-----

> [!WARNING]
> Depgraph generates [Graphviz](https://graphviz.org/) Dot files. You will need to install the Graphviz
> on your operating system to access the `dot` binary.

The `depgraph` script will output a Graphviz Dot file to `stdout`. You can
pipe it directly to the `dot` binary to generate a diagram:

```
depgraph | dot -Tsvg -ograph.svg
```

Specify a composer file:

```
depgraph path/to/my/composer.json | dot -Tsvg -ograph.svg
```

Do the slower things and output to a PNG:

```
depgraph path/to/my/composer.json --audit --outdated | dot -Tpng -ograph.png
```

---

🩸 _made by with blood and tears by **you** 🫵_
