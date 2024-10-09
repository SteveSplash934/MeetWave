MeetWave/
│
├── setup/
│ ├── config.php # Configuration file for customizable settings
│ ├── setup.php # Setup script for user customization
│
├── src/
│ ├── config/
│ │ ├── database.php
│ │ └── config.php # Original config file
│ │
│ ├── database/
│ │ ├── database.sql
│ │
│ ├── public/
│ │ ├── index.php
│ │ ├── css/style.css
│ │ ├── js/script.js
│ │ ├── img/
│ │ ├── fonts/
│ │ ├── uploads/
│ │ ├── assets/
│ │ └── .htaccess
│ ├── templates/
│ │ ├── header.php
│ │ ├── footer.php
│ │ ├── meeting.php
│ │ ├── login.php
│ │ ├── register.php
│ │ └── dashboard.php
│ ├── controllers/
│ │ ├── UserController.php
│ │ ├── MeetingController.php
│ │ └── ParticipantController.php
│ ├── models/
│ │ ├── User.php
│ │ ├── Meeting.php
│ │ └── Participant.php
│ ├── views/
│ │ ├── login.view.php
│ │ ├── register.view.php
│ │ └── meeting.view.php
│ ├── middlewares/
│ │ └── AuthMiddleware.php
│ └── utils/
│ └── helpers.php
│
├── .gitignore
├── composer.json
└── README.md
