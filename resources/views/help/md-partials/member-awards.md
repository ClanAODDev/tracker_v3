# Member Awards Image Generator

Generate a dynamic image of your latest earned awards. 

## Endpoints

### 1. **Single-Line Award Display**
`GET /members/{member}/my-awards.png`

This endpoint dynamically generates a single-line image of evenly centered awards for a specific 
member. Details on customization options are provided [here](#single-line-award-display).

Awards are evenly centered on a fixed image.

Note: specific iterations to include customizations of an award are cached for up to an hour.

---

### 2. **Award Cluster Display**
`GET /members/{member}/my-awards-cluster.png`

This endpoint generates a clustered layout of awards for a specific member, organizing the awards in rows and 
columns. 

There are no customization options as this version scales to the number of awards available, up to 6.

---

### Comparison

| Feature                   | Single-Line Layout                    | Cluster Layout                         |
|---------------------------|---------------------------------------|---------------------------------------|
| **Endpoint**              | `/members/{member}/my-awards.png`     | `/members/{member}/my-awards-cluster.png` |
| **Max Awards Displayed**  | Up to 4                              | Up to 6                               |
| **Layout**                | Horizontal                           | Grid                                  |
| **Customization Options** | Yes                                  | No                                    |
| **Image Dimensions**      | Fixed size, adjusted per award count | Fixed at 60x60 pixels per award       |
| **Spacing**               | Dynamically calculated               | Fixed at 10 pixels between awards     |

---

## Query Arguments

### **Customization Options**

1. **`award_count`**
    - **Description**: Specifies the number of awards to display.
    - **Type**: Integer
    - **Range**: 1 to 4
    - **Default**: The total number of awards available for the member.

2. **`text_offset`**
    - **Description**: Adjusts the vertical spacing between award images and their labels.
    - **Type**: Integer
    - **Range**: 1 to 45
    - **Default**: 20

3. **`image_offset`**
    - **Description**: Shifts the award images vertically.
    - **Type**: Integer
    - **Range**: 1 to 45
    - **Default**: 20

4. **`font`**
    - **Description**: Selects the font type for award labels.
    - **Type**: String
    - **Options**:
        - `ttf` (TrueType Font)
        - `bitmap` (non-antialiased)
    - **Default**: `ttf`

5. **`font_size`**
    - **Description**: Adjusts the font size of the award labels.
    - **Type**: Integer
    - **Range**:
        - For `ttf`: 7 to 12
        - For `bitmap`: 1 to 5
    - **Default**:
        - For `ttf`: 7
        - For `bitmap`: 1

6. **`text_container_width`**
    - **Description**: Sets the maximum width for the text before wrapping to a new line.
    - **Type**: Integer
    - **Minimum**: 1
    - **Default**: 100

7. **`text_transform`**
    - **Description**: Controls the case transformation of award labels.
    - **Type**: String
    - **Options**:
        - `upper` (Transforms all text to uppercase)
        - *Empty* (Preserves original case)
    - **Default**: Preserves original case.

---

## Example Usage

### Basic Example
```shell
GET /members/31732-MSgt-Guybrush/my-awards-cluster.png

GET /members/31732-MSgt-Guybrush/my-awards.png?award_count=3&text-offset=25&font=bitmap&font-size=4
```
