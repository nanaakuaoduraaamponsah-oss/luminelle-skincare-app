# luminelle-skincare-app
# 🌸 LuminElle - Skincare Tracking Application

## 📖 Project Description
LuminElle is a comprehensive full-stack web application designed to help users organize, track, and optimize their skincare routines. Built with PHP, MySQL, and modern web technologies, it provides personalized skincare guidance, routine tracking, and progress visualization.

## 🔗 Live Application
**URL:**  
**Note:** Currently running on local development server

## ✨ Key Features

### 1. **Elle Guide** 🔍
- 6-question interactive skin type quiz
- Personalized results with recommendations
- Results stored in user profile

### 2. **Lumin Routine** 💆‍♀️
- Database of 10 common skin concerns
- Detailed information including:
  - Causes and descriptions
  - Foods to eat and avoid
  - Recommended ingredients
  - Myth-busting facts
  - Actionable suggestions

### 3. **Glow Calendar** 📅
- Daily routine tracking
- Morning and evening routine logging
- Photo upload functionality (progress tracking)
- Color-coded completion status
- Streak tracking (current and longest)
- Copy previous day's routine feature

### 4. **Progress Gallery** 📸
- Visual timeline of skincare journey
- Filter by morning/evening photos
- Lightbox zoom functionality
- Date-labeled photo organization

### 5. **Skincare Assistant** 💬
- 25 predefined Q&A pairs
- Chat-style interface
- Search functionality
- Categories: Skincare Tips & App Usage

### 6. **Progress Visualization** 📊
- Bar chart: Last 30 days activity
- Pie chart: Completion status distribution
- Statistical insights
- Completion rate tracking

## 🛠️ Technologies Used

### Backend
- **PHP 8.2.12** - Server-side logic
- **MySQL** - Database management
- **Apache 2.4** - Web server

### Frontend
- **HTML5** - Structure
- **CSS3** - Styling with custom pink theme
- **JavaScript (ES6)** - Interactivity
- **Chart.js** - Data visualization

### Security
- Password hashing (password_hash)
- Prepared statements (SQL injection prevention)
- Session management
- File upload validation
- Input sanitization

## 🗄️ Database Schema
Database schema: See luminelle.sql

### Tables (4 main tables):

**1. users**
- id, first_name, last_name, email, password, created_at

**2. skin_quiz_results**
- id, user_id, skin_type, question_1 through question_6, date_taken

**3. skin_concerns**
- id, concern_name, icon, description, foods_to_eat, foods_to_avoid, recommended_ingredients, myth_1_title, myth_1_truth, myth_2_title, myth_2_truth, suggestions

**4. journal_entries**
- id, user_id, entry_date, morning_routine, evening_routine, morning_photo, evening_photo, notes, completion_status, created_at

**5. chatbot_qa**
- id, category, question, answer, order_num

## 🎯 Key Algorithms & Logic

### Streak Calculation Algorithm
Calculates consecutive days with journal entries
// Iterates through entries in descending order
// Compares consecutive dates
// Increments counter for consecutive days
// Breaks on first gap

### Quiz Scoring Logi
// Counts frequency of each answer (A-E)
// Maps most common answer to skin type:
// A = Dry, B = Normal, C = Combination, D = Oily, E = Sensitive

### Color-Coded Calendar
- White (#FFFFFF) = No entry
- Baby Pink (#FFD6E8) = Incomplete
- Fuchsia (#FF69B4) = Half-done
- Bright Pink (#FF1493) = Complete

## 📱 Features Breakdown
### User Authentication
- Secure signup with validation
- Password hashing (bcrypt)
- Session-based authentication
- Protected routes

### File Upload System
- Image validation (JPG, PNG)
- Size limit: 5MB
- Unique filename generation
- Organized storage structure

### Data Visualization
- Chart.js integration
- Real-time data updates
- Interactive tooltips
- Responsive charts

### Search & Filter
- Real-time search in chatbot
- Gallery filtering (morning/evening/all)
- Calendar month navigation

## 📊 Project Statistics
- **Total Files:** 25+ PHP files
- **Database Records:** 200+ (10 concerns × multiple fields + 25 Q&A)
- **Lines of Code:** 3000+
- **Development Time:** 13 days
- **Features:** 8 major features

## 🎨 Design Philosophy
- Pink color scheme for skincare aesthetic
- User-friendly interface
- Mobile-responsive design
- Consistent navigation
- Clear visual hierarchy

## 🔐 Security Features
- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- XSS prevention (htmlspecialchars)
- File upload validation
- Session security
- CSRF protection considerations

  ## 📹 Video Demo
-link

  ## 👤 Author
Nana Akua Oduraa Amponsah 
Web Technology Final Project  - Luminelle Skin Care App
Ashesi University 
December 2024

This project was developed as part of the Web Technology course final assessment.


---

