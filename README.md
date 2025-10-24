# AI Agent Integration

This module provides a set of REST API endpoints to allow an AI agent to interact with an Adobe Commerce store. It exposes functionality for product discovery and checkout management.

## Installation

1.  Enable the module:
    ```bash
    bin/magento module:enable SinhaR_AiAgenticIntegration
    ```

2.  Run the setup upgrade command:
    ```bash
    bin/magento setup:upgrade
    ```

3.  Compile the dependency injection configuration:
    ```bash
    bin/magento setup:di:compile
    ```

4.  Clean the cache:
    ```bash
    bin/magento cache:clean
    ```

## API Endpoints

All endpoints are relative to your store's base URL. For example: `https://yourstore.com/rest/V1/`...

---

### 1. Product Discovery

Exposes the product catalog in a structured format.

*   **Endpoint:** `GET /V1/aiagent/products`
*   **Method:** `GET`
*   **Authentication:** None (Anonymous)

**Example Request:**

```bash
cURL -X GET "https://yourstore.com/rest/V1/aiagent/products"
```

**Example Response:**

```json
[
    {
        "sku": "24-MB01",
        "name": "Joust Duffle Bag",
        "price": 34
    },
    {
        "sku": "24-MB04",
        "name": "Push It Messenger Bag",
        "price": 45
    }
]
```

---

### 2. Create Checkout

Initiates a new checkout and returns a cart ID.

*   **Endpoint:** `POST /V1/aiagent/checkout`
*   **Method:** `POST`
*   **Authentication:** None (Anonymous)

**Example Request:**

```bash
cURL -X POST "https://yourstore.com/rest/V1/aiagent/checkout"
```

**Example Response:**

```json
"Successfully created a new checkout with cart ID: cart_5f7e1b2c3d4e5"
```

---

### 3. Update Checkout

Adds or modifies items in the cart.

*   **Endpoint:** `PUT /V1/aiagent/checkout/{cartId}`
*   **Method:** `PUT`
*   **Authentication:** None (Anonymous)
*   **Parameters:**
    *   `cartId` (string, required): The ID of the cart to update.
    *   `items` (array, required): An array of items to add/update. (The structure of this array would be defined in a more complete implementation).

**Example Request:**

```bash
cURL -X PUT "https://yourstore.com/rest/V1/aiagent/checkout/cart_5f7e1b2c3d4e5" \
-H "Content-Type: application/json" \
-d '{
    "items": [
        { "sku": "24-MB01", "qty": 2 }
    ]
}'
```

**Example Response:**

```json
true
```

---

### 4. Complete Checkout

Finalizes the purchase using a payment token.

*   **Endpoint:** `POST /V1/aiagent/checkout/{cartId}/complete`
*   **Method:** `POST`
*   **Authentication:** None (Anonymous)
*   **Parameters:**
    *   `cartId` (string, required): The ID of the cart to complete.
    *   `paymentToken` (string, required): A token from a payment provider like Stripe.

**Example Request:**

```bash
cURL -X POST "https://yourstore.com/rest/V1/aiagent/checkout/cart_5f7e1b2c3d4e5/complete" \
-H "Content-Type: application/json" \
-d '{
    "paymentToken": "tok_12345ABCDE"
}'
```

**Example Response:**

```json
true
```

---

### 5. Cancel Checkout

Cancels an active checkout.

*   **Endpoint:** `DELETE /V1/aiagent/checkout/{cartId}`
*   **Method:** `DELETE`
*   **Authentication:** None (Anonymous)
*   **Parameters:**
    *   `cartId` (string, required): The ID of the cart to cancel.

**Example Request:**

```bash
cURL -X DELETE "https://yourstore.com/rest/V1/aiagent/checkout/cart_5f7e1b2c3d4e5"
```

**Example Response:**

```json
true
```
