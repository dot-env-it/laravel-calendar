# DotEnv Calendar for Laravel

[![Latest Stable Version](https://img.shields.io/packagist/v/dot-env-it/laravel-calendar.svg?style=flat-square)](https://packagist.org/packages/dot-env-it/laravel-calendar)
[![Total Downloads](https://img.shields.io/packagist/dt/dot-env-it/laravel-calendar.svg?style=flat-square)](https://packagist.org/packages/dot-env-it/laravel-calendar)
[![License](https://img.shields.io/packagist/l/dot-env-it/laravel-calendar.svg?style=flat-square)](https://packagist.org/packages/dot-env-it/laravel-calendar)

**A high-performance, reactive calendar suite for Laravel applications.**

Built on the robust **FullCalendar 6** core and powered by ** Livewire ** and **Alpine.js**, this package provides a seamless bridge between your Eloquent models and a sophisticated frontend schedule. Transform any database record into a calendar event with zero boilerplate and maintain a professional, desktop-grade user experience on your Dashboard.

-----


## 🚀 Key Capabilities

* **Eloquent-to-Calendar Mapping:** Seamlessly integrate any model using the `HasCalendarEvents` trait. You can add multiple model's events by just adding `HasCalendarEvents` trait.
* **Livewire Reactivity:** Real-time state synchronization; your calendar reflects database changes without a single page refresh.
* **Professional Grid Engine:** Full support for `dayGridMonth`, `timeGridWeek`, and `listWeek` layouts.
* **Intelligent Smart Filtering:** Integrated filtering that automatically manages visibility based on your data density.
* **Interactive Persistence:** Native drag-and-drop support for rescheduling, with automatic database persistence.
* **Dynamic UI Orchestration:** Support for custom event prefixes, mapping of custom color attributes, and deep-linking to specific module resources.

-----
## Become a sponsor [![](https://img.shields.io/static/v1?label=Sponsor&message=%E2%9D%A4&logo=GitHub&color=%23fe8e86)](https://github.com/sponsors/jagdish-j-p)

Your support allows me to keep this package free, up-to-date and maintainable. Alternatively, you can **[spread the word!](http://twitter.com/share?text=I+am+using+this+cool+Laravel+Calendar+package&url=https://github.com/dot-env-it/laravel-calendar&hashtags=Calendar,PHP,Laravel)**

-----

## 📦 Installation

```bash
composer require dot-env-it/laravel-calendar
```

-----

## 🛠️ Implementation Guide

### 1\. Configure Your Model

Apply the trait and define the `calendar_fillable` schema. You can map `color` to a standard database column or a **custom attribute** (Accessor) for dynamic styling or `class` to add bootstrap/tailwind class.

```php
use DotEnv\Calendar\Traits\HasCalendarEvents;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Task extends Model
{
    use HasCalendarEvents;

    protected $calendar_fillable = [
        'title'      => 'name',                    // Source column for event labels
        'date_field' => 'next_date',             // required if `date` is custom attribute 
        'date'       => 'due_date',                // Source column for temporal data
        'color'      => 'event_color',             // Maps to a DB column or custom attribute
        'class'      => 'bootstrap/tailwind classes name',  // Maps to a DB column or custom attribute
        'route'      => 'tasks.show',              // Target named route for click actions
        'link_id'    => 'id',                      // Optional Primary key for route resolution,(required only if primary key is not id)
        'prefix'     => '📌 ',                     // Branding/Iconography prefix
        'editable'   => 'can_edit'                    // You can make any record editable / draggable by adding any boolean value, i.e. create attribute which returns some boolean condition
    ];

    /**
     * Example: Custom attribute for editable
     */
    protected function canEdit(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->role === 'admin',
        );
    }
    
    /**
     * Example: Custom attribute for dynamic coloring
     */
    protected function eventColor(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->priority === 'high' ? '#e74c3c' : '#3498db',
        );
    }
}
```

### 2\. Frontend Dependencies

Ensure your application layout includes the industry-standard Select2 and FullCalendar 6 assets to power the interactive components.

-----

## 🖥️ Production Usage

Deploy the calendar component directly into your Dashboard or any administrative view:

```html
<livewire:dot-env-calendar />
```

-----

### Conditional Filtering (Advanced)

To control which records appear on the calendar (e.g., only active tasks or user-specific events), you can use two primary methods:

#### A. Using Global Scopes

Since the calendar fetches events via your Eloquent models, applying a [Global Scope](https://www.google.com/search?q=https://laravel.com/docs/11.x/eloquent%23global-scopes) will automatically filter the events displayed.

```php
// In your Model
protected static function booted(): void
{
    static::addGlobalScope('active', function (Builder $builder) {
        $builder->where('status', 'active');
    });
}
```

#### B. Using `applyCalendarFilters`

For more control—or to apply filters specifically to the calendar without affecting the rest of your application—you can define an `applyCalendarFilters` method in your model. The package will automatically detect and call this method when fetching events.

```php
use Illuminate\Database\Eloquent\Builder;

class Task extends Model
{
    use HasCalendarEvents;

    /**
     * Specifically filter records for the calendar view
     */
    public function applyCalendarFilters(Builder $query): Builder
    {
        return $query->where('user_id', auth()->id())
                     ->whereNull('deleted_at')
                     ->where('is_private', false);
    }
}
```

-----

* **Scoped Queries:** Use standard Laravel Global Scopes or the dedicated `applyCalendarFilters` method to ensure users only see the data they are authorized to view.

-----

**Note:** Using `applyCalendarFilters` is the recommended approach if you want to display a subset of data on the calendar (like "Upcoming Deadlines") while keeping the model's default behavior untouched elsewhere in your app.


### Smart Filtering Logic

The component features an intelligent filtering system driven by your configuration and data:

* **Automatic Hiding:** The filter UI will **automatically hide** itself if only one model type is registered (preventing redundant UI elements).
* **Global Toggle:** Enable or disable the filtering capability globally via the configuration file.
* **Manual Override:** Force the filter state at the component level:
  ```html
  <livewire:dot-env-calendar :show-filter="false" />
  ```

-----

## ⚙️ Global Configuration

Publish and refine the `config/laravel-calendar.php` file to align the calendar with your organization’s operational settings:

```php
return [
    'enable_filter' => true, // Global default for the Select2 filter
    'default_time' => '10:30:00', // if database has date field then to show this time instead of 00:00:00 

    /*
    |--------------------------------------------------------------------------
    | Dynamic Event Styling
    |--------------------------------------------------------------------------
    | The package automatically colors events based on their date.
    | You can use Bootstrap/Tailwind classes or HEX codes.
    */
    'colors' => [
        'past' => ['class' => 'bg-danger text-white', 'color' => '#dc3545'],
        'today' => ['class' => 'bg-warning text-dark', 'color' => '#ffc107'],
        'tomorrow' => ['class' => 'bg-info text-white', 'color' => '#17a2b8'],
        'future' => ['class' => 'bg-success text-white', 'color' => '#28a745'],
    ],
    
    'businessHours' => [
        'daysOfWeek' =>, // Standard Work Week
        'startTime' => '08:30',
        'endTime' => '17:30',
    ],
    'hideWeekends' => false,
];
```

-----

## ⌨️ Developer Events

Leverage custom browser events to integrate the calendar with your application's wider notification ecosystem:

* **`dot-env-calendar:refreshed`**: Dispatched upon successful data synchronization.
* **`dot-env-calendar:create-new-event`**: Dispatched upon day click
* **`dot-env-calendar:view-event-details`**: Dispatched upon event click with eventId
* **`dot-env-calendar:event-updated`**: Dispatched following successful database update via drag-and-drop.

-----

## 📄 License

The MIT License (MIT). For further details, please consult the License File.
