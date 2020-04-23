# IoT Platform Project (WEB) #

> Qualcomm Institute 동계 현장학습 인턴십 프로젝트


### 요구사항 정의
-----------------
 - 요구사항: https://trello.com/b/H4h41yy8/%EC%9A%94%EA%B5%AC-%EC%82%AC%ED%95%AD
 - 기능 (Trello): https://trello.com/b/4OEbonfx/function
 - 기능 (Text): https://docs.google.com/document/d/1zQsi_opPK-Dt2oowp8CeBQYJ6_cYFcPh2jb3af2xkm4/edit
 - 데이터베이스: https://trello.com/b/hi0eKYsd/database
 

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
> PHP, Slim framework, JWT(Json Web Token)

 - Frontend: https://bitbucket.org/16byte/teamb-iot/src/master/public/
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
     
 
 - Backend: https://github.com/64byte/teamb-iot/tree/master/apps/iot
   > Models
   * BoardModel: https://github.com/64byte/teamb-iot/tree/master/apps/iot/models
   * UserModel: https://github.com/64byte/teamb-iot/blob/master/apps/iot/models/UserModel.php
  
  
   > Controller
   * API(Rest API) Controller: https://github.com/64byte/teamb-iot/blob/master/apps/iot/controllers/APIController.php
   * Board Controller: https://github.com/64byte/teamb-iot/blob/master/apps/iot/controllers/BoardController.php
   * Index Controller: https://github.com/64byte/teamb-iot/blob/master/apps/iot/controllers/IndexController.php
   * User Controller: https://github.com/64byte/teamb-iot/blob/master/apps/iot/controllers/UserController.php
  
  
   > Routers: https://github.com/64byte/teamb-iot/blob/master/apps/iot/routers/v1.default.router.php
  
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
