# IoT Platform Project (WEB) #

> Qualcomm Institute 동계 현장학습 인턴십 프로젝트
  - 사용자가 미세먼지를 측정할 수 있는 기기(Uno board)를 이용하여 미세먼지를 측정하고 안드로이드 어플리케이션과 연동하여 실시간/기록 데이터를 조회할 수 있으며 즉시 웹서버에 저장합니다. 그 때문에 어플리케이션을 이용한 조회 뿐만 아니라 웹을 통해 해당 데이터를 조회할 수 있습니다.
  (부가적으로 사용자의 심박수도 측정하여 저장 및 조회가 가능합니다)
  
  | 조회가 가능한 정보는 다음과 같습니다.
    - 대기 데이터 (실시간/기록 정보)
      - No2, O3, CO, PM10, PM2.5, SO2, Temperature, location information
    - AQI(Air Quality Index) 데이터 (실시간/기록 정보) / (https://en.wikipedia.org/wiki/Air_quality_index)
      - No2, O3, Co, PM10, PM2.5, SO2, Temperatrue, location information
    - 심박수 (실시간/기록 정보)
      - HeartRate, RR Interval, location information
     
### 요구사항 정의
-----------------
 - 요구사항: https://trello.com/b/H4h41yy8/%EC%9A%94%EA%B5%AC-%EC%82%AC%ED%95%AD
   <img src="https://github.com/64byte/teamb-iot/blob/master/resource/req_sepc.png"></img>
 
 - 기능 (Trello): https://trello.com/b/4OEbonfx/function
   <img src="https://github.com/64byte/teamb-iot/blob/master/resource/function.png"></img>
 
 - 기능 (Text): https://docs.google.com/document/d/1zQsi_opPK-Dt2oowp8CeBQYJ6_cYFcPh2jb3af2xkm4/edit
 
 - 데이터베이스: https://trello.com/b/hi0eKYsd/database
   <img src="https://github.com/64byte/teamb-iot/blob/master/resource/erd_prototype.png"></img>
 

### 설계
-----------------
 - 시스템 설계
   <img src="https://github.com/64byte/teamb-iot/blob/master/resource/System_Architecture.png"></img>

- 흐름도: https://drive.google.com/drive/folders/1iLiAPrX4twRlZDoDkcp3Yhi5uKnuCBSn

- 데이터베이스 ERD

  <img src="https://github.com/64byte/teamb-iot/blob/master/resource/erd.png"></img>

- Restful API 명세서: https://docs.google.com/document/d/1XRqVN9bfgP0eXDqtqHFPX3g57-_Dghw42GGNJr1QfaY/edit?usp=sharing


### 구현
-----------------
> Stack: PHP, Slim framework, JWT(Json Web Token), MySQL, HTML5, CSS3, Javascript, BootStrap, D3.js
  - Backend: https://github.com/64byte/teamb-iot/tree/master/apps/iot
    * Models
      * BoardModel: https://github.com/64byte/teamb-iot/tree/master/apps/iot/models
      * UserModel: https://github.com/64byte/teamb-iot/blob/master/apps/iot/models/UserModel.php
  
    * Controller
      * API(Rest API) Controller: https://github.com/64byte/teamb-iot/blob/master/apps/iot/controllers/APIController.php
      * Board Controller: https://github.com/64byte/teamb-iot/blob/master/apps/iot/controllers/BoardController.php
      * Index Controller: https://github.com/64byte/teamb-iot/blob/master/apps/iot/controllers/IndexController.php
      * User Controller: https://github.com/64byte/teamb-iot/blob/master/apps/iot/controllers/UserController.php
      
    * Routers
      * https://github.com/64byte/teamb-iot/blob/master/apps/iot/routers/v1.default.router.php

 - Frontend: https://github.com/64byte/teamb-iot/tree/master/public
   > Views: https://github.com/64byte/teamb-iot/tree/master/apps/iot/views
   * Index View
     * https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/index/index.phtml
   
   * Signup View
     * Signup: https://github.com/64byte/teamb-iot/tree/master/apps/iot/views/signup
     * Success: https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/signup/success.phtml
     * Confirm: https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/signup/confirm.phtml
     
   * Signin View
     * https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/signin/signin.phtml
     
   * Profile View
     * Profile: https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/profile/profile.phtml
     * Boards: https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/profile/boards.phtml
     * Password: https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/profile/password.phtml
   
   * Air Polluation View
     * Historical AP: https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/airpollution/historyap.phtml
     * Realtime AP: https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/airpollution/realtimeap.phtml
     
   * AQI Chart View
     * AQI Chart: https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/aqi/aqichart.phtml
     * AQI Maps: https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/aqi/aqimaps.phtml
     * Historical AQI: https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/aqi/historyaqi.phtml
     
   * HeartRate View
     * Historical HR: https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/heartrate/historyhr.phtml
     * Realtime HR: https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/heartrate/realtimehr.phtml
   
   * ForgetPassword View
     * ForgetPassword: https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/forgotPwd/forgotpwd.phtml
     * Confirm: https://github.com/64byte/teamb-iot/blob/master/apps/iot/views/forgotPwd/confirm.phtml
    

### 구현 화면
-----------------
 - Main (Sign in)
   <img src="https://github.com/64byte/teamb-iot/blob/master/resource/main_page.png"></img>
   
 - Sign up
   <img src="https://github.com/64byte/teamb-iot/blob/master/resource/signup_page.png"></img>
   
 - Forgot Password
   <img src="https://github.com/64byte/teamb-iot/blob/master/resource/forgetpwd_page.png"></img>

### 주차별 발표 자료
-----------------
 - 1주차: https://docs.google.com/presentation/d/1oqsBIINXGFWxJz5M1wtpwAXQXQ-OktDlyaVIFubMfas/edit?usp=sharing
 - 2주차: https://docs.google.com/presentation/d/1xxOQlKMAcu4D1ZcA861a893QMlyv9euxM0Ugi2KtZC0/edit?usp=sharing
 - 3주차: https://docs.google.com/presentation/d/1c7ulkvwbrzEHiOP2kBBetZ5zOKeYGEKLshQsE0R6lls/edit?usp=sharing
 - 4주차: https://docs.google.com/presentation/d/1Ocv5BFY3Wn_AWow0OaZI6S6QGqX8ppuicvlx62pcMok/edit?usp=sharing
 - 5주차: https://docs.google.com/presentation/d/1p3aaHX6-thta5gTPIyH104JW23VqjBbhsoSrpOVcuHI/edit?usp=sharing
 - Final: https://drive.google.com/file/d/1b6rdLwlYMu3-jNMIQejgfUvzUPkhDgT7/view?usp=sharing

## License
This project is released under the MIT public license.
