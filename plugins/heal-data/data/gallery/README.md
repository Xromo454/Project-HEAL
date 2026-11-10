# Gallery

One JSON file per photo in this folder; the image file goes in `images/`.
Same format as the Astro site (src/content/gallery).

| Field          | Required  | Notes                                                    |
| -------------- | --------- | -------------------------------------------------------- |
| `image`        | yes       | `./images/<file>.jpg`, ideally at least 1600 px wide     |
| `alt`          | yes       | What is in the photo, for screen readers                 |
| `section`      | yes       | `moodboard`, `stock`, `limassol` or `locations`          |
| `caption`      | no        | Short text under the photo                               |
| `credit`       | stock     | Photographer, e.g. `Jane Doe / Pexels`                   |
| `creditUrl`    | no        | Link to the photo on Pexels / the photographer           |
| `organisation` | locations | Partner id (file name in data/partners), e.g. `cut`      |
| `location`     | locations | City or branch, e.g. `Nicosia`                           |
| `order`        | no        | Lower numbers come first (default 0)                     |

Before publishing photos of people: everyone recognisable must have given written consent (GDPR).
