# Search

LGBE2 provides a comprehensive search system that allows users to find communities, posts, and comments across the platform.

## Features

- Search for communities by name and description
- Search for posts by title and content
- Search for comments by content
- Filter search results by type (community, post, comment)
- Sort search results by relevance or recency

## Implementation

The search system is implemented using the `SearchController` and related components. The system supports full-text search and filtering.

### Backend Components

- `App\Http\Controllers\SearchController`: Handles search operations
- Database full-text indexes on searchable columns

### Frontend Components

- `resources/js/pages/Search/Index.vue`: Search interface
- `resources/js/components/Search/SearchBar.vue`: Search input component
- `resources/js/components/Search/SearchResults.vue`: Search results component

## Search Algorithm

The search algorithm uses database full-text search capabilities to find relevant content. Results are ranked by relevance, which is determined by:

- Exact matches in titles or names (highest priority)
- Partial matches in titles or names
- Matches in content or descriptions
- Recency of content (optional)

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/search` | GET | Search for content across the platform |

## Request Parameters

- `q`: Search query string
- `type`: Optional filter for result type (community, post, comment)
- `sort`: Optional sort order (relevance, recent)
