# Featured news and promotions

News and promotion titles, introductions, images, ordering, and article bodies are stored in `featured_articles` and `featured_article_bodies`. The versioned seed sources are `database/seeders/data/featured-articles.json` and the readable HTML files in `database/seeders/data/featured-article-bodies/`.

On a new installation, run migrations, then seed only this editorial content:

```bash
php artisan migrate --force
php artisan db:seed --class=FeaturedContentSeeder --force
```

The seeder can be run again after updating the versioned content. It updates rows by slug without creating duplicates. Database edits take precedence when pages render; rerunning the seeder restores the versioned values. Article bodies contain trusted HTML maintained with this repository and are rendered as HTML on the public article page. Do not put untrusted user input in these fields.
