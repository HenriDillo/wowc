# WOW Carmen Order and Inventory Management System

## 📌 Background of the Organization
WOW Carmen Handicrafts is a cooperative organization in the manufacturing industry that produces eco-friendly handicrafts using dried water hyacinths. Based in Purok 5-A, Tuganay, Carmen, Davao del Norte, the organization serves both local and international customers seeking sustainable, handcrafted products such as bags, baskets, home décor, and souvenirs.  

Its customer base includes individual buyers, retailers, and environmentally conscious consumers. Originating as an informal group aiming to turn invasive water hyacinths into livelihood opportunities, WOW Carmen has grown into a formal organization that promotes environmental sustainability, traditional craftsmanship, and community empowerment.

## 🛠️ Modules
The system is designed with the following modules:  

- **Customer Order Module**  
  Allows customers to place and track their orders.  

- **Inventory Management Module**  
  Keeps records of product stock, updates quantities, and monitors availability.  

- **Order Management Module**  
  Handles order processing, fulfillment, and updates order status from pending to completion.  

## ⚙️ How to Clone, Setup, and Run the Project

### Prerequisites
Before running the project, ensure that you have the following installed on your system:
- PHP >= 8.x  
- Composer  
- MySQL
- XAMPP
- Node.js & NPM  

### Installation Steps
1. **Clone Repository**
   ```bash
   git clone https://github.com/HenriDillo/wowc.git
   cd wowc
2. **Install Dependencies**
   ```bash
   composer install
   npm install && npm run dev
   Setup Environment

3. **Copy .env.example to .env**
    ```bash
    Configure database connection, mail settings, and app details inside .env
4. **Generate Application Key**
    ```bash
    php artisan key:generate
5. **Run Database Migrations & Seeders**
    ```bash
    php artisan migrate --seed
6. **Start Development Server**
    ```bash
    php artisan serve
## Authors
This project was developed by:  
- **Henri James Dillo**  
- **Janstin Ysvert Yamota**  
- **Kobe Michael Lopez**

