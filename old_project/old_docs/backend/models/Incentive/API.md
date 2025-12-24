# Incentive Model API Integration

The `Incentive` model is primarily used for tracking and managing commissions and rewards within the system. While direct CRUD operations on `Incentive` records might not be exposed as public API endpoints, its data is often generated and consumed by other API processes (e.g., when an order is placed, an incentive might be created).

## Available Endpoints

*   **`GET /account/incentives`**: 
    *   **Controller:** `App\Http\Controllers\Api\Account\IncentiveController@index`
    *   **Purpose:** Retrieves a list of incentives for the authenticated user.
    *   **Request:** Authenticated request.
    *   **Response:** Paginated list of incentive records.
*   **`GET /account/incentives/{uuid}`**: 
    *   **Controller:** `App\Http\Controllers\Api\Account\IncentiveController@show`
    *   **Purpose:** Retrieves details of a specific incentive for the authenticated user.
    *   **Request:** Authenticated request, `uuid` of the incentive.
    *   **Response:** Details of the incentive record.

## Feature Completeness (API Side)

*   [x] Retrieve user's incentives.
*   [x] Retrieve details of a specific user incentive.
*   [ ] API for creating incentives (likely internal or triggered by other events).
*   [ ] API for updating/deleting incentives (likely internal or restricted to admin).

## Suggestions

*   Ensure that the API endpoints for retrieving incentives include appropriate filtering, sorting, and pagination options to handle large datasets efficiently.
*   Implement robust authorization to ensure users can only access their own incentive records.
*   Consider adding API endpoints for aggregated incentive data or summaries for reporting purposes.
