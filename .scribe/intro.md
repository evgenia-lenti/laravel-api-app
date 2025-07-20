# Introduction

Exchange Rate API provides access to currency exchange rates fetched from the European Central Bank (ECB).

<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>

    This documentation provides all the information you need to work with our Exchange Rate API.

    The API allows you to:
    - Retrieve a list of all available exchange rates (fetched from the ECB API in real-time)
    - Get details for a specific exchange rate

    All exchange rates are sourced from the European Central Bank (ECB). The exchange rates are fetched from the ECB API every time the index endpoint is called, stored in the database, and then returned as a paginated response.

    <aside>As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
    You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).</aside>

