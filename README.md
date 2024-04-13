### Wixable
Automatically imports (one-way sync from Wix CMS) data items from Wix Headless CMS to your app's database.

#### Installation

Install package:
`composer required polarize/wixable`

Add your Wix API credentials to your environment file:
```
    // .env

    WIX_API_KEY="..."
    WIX_ACCOUNT_ID="..."
    WIX_SITE_ID="..."
```

Publish the migration for Wixables "wixable_data_items" table:
`php artisan vendor:publish --tag=wixable.migrations`

Run migrations:
`php artisan migrate`



#### Setup your models
Add a new class to your app/Models folder that extends `Wixable\Wixable`. Repeat for each of the **Data Collections** in Wix that you would like to keep synced (the model name should be the singular version of the data collection's ID).

For example, in the case of my "Breakfast Sandwich Reviews" collection in Wix (witch might have the **Data Collection ID** of "BreakfastSandwichReviews"), I would do the following:

1. Run the artisan make command:
`php artisan make:model BreakfastSandwichReview`

2. Update the new model to extend the `Wixable\Wixable` abstract class:
```
<?php

namespace App\Models;

use Wixable\Wixable;

class BreakfastSandwichReview extends Wixable
{
    //
}
```

**Note:** You can also set the data collection name via the `$dataCollectionId` property like so...
```
class Reviews extends Wixable
{
    protected string $dataCollectionId = 'BreakfastSandwichReviews';

```
