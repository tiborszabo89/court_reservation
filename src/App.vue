<template>
  <div class="reservation-container" v-if="userdata.id != 0">
    <div class="res-col-wrap">
      <div class="days">
        <div class="day">
          <div class="datepicker"><input type="date" v-model="selectedDate" @change="selectThanSetDate()"></div>
        </div>
        <div class="nav">
          <div class="prev" @click="dateBack()">{{courtResStrings.prevdate}}</div>
          <div class="next" @click="dateNext()">{{courtResStrings.nextdate}}</div>
        </div>
        <div class="is_admin" v-if="userdata.is_admin == 1">
          Admin mode
        </div>
        <div class="berlet" v-if="userdata.berlet > 0">
          Bérlet: {{userdata.berlet}} alkalom
        </div>
      </div>
    </div>
    <div class="res-col-wrap reservation-calendar">
      <div class="res-column" v-for="(res, id) in results" :key="id">
        <div class="courtname" v-bind:class="res.text">{{res.name}}</div>
        <div class="timeslots" v-bind:class="res.text">
          <div class="time" v-for="n in 14" :key="n">
            <div v-bind:class="'reservable ' + isClicked(res.name, n+6)" v-if="isCourtReservable(n+6, res) && isCourtAvailableThisTime(n+6, res.reserved)" @click="updateSelectedTimeslots(res.id, res.name, n+6)">
              {{courtResStrings.reservable}}: {{n+6}}:00 - {{n+7}}:00
              <div class="res-tooltip" v-if="res.darktime <= n+6"><div class="lightbulb-icon"></div>
                <span class="tooltiptext">{{courtResStrings.darktime}}</span>
              </div>
              <div class="res-tooltip" v-if="lessThan24H(n+6) === 1"><div class="danger-icon"></div>
                <span class="tooltiptext">{{courtResStrings.last_minute}}</span>
              </div>
            </div>
            <div class="non-reservable" v-bind:class="(whoReservedIt(n+6, res.reserved).length > 0 && (whoReservedIt(n+6, res.reserved)[0].display_name == userdata.name || whoReservedIt(n+6, res.reserved)[0].display_name == userdata.full_name) || userdata.is_admin == 1) ? 'mine' : ''" v-else>
              {{n+6}}:00 - {{n+7}}:00
              <div class="who" v-if="userdata.is_admin == 1">
                <div v-if="whoReservedIt(n+6, res.reserved).length > 0">
                  {{whoReservedIt(n+6, res.reserved)[0].display_name}}
                  <div class="res-btns">
                    <div class="res-tooltip" v-if="whoReservedIt(n+6, res.reserved)[0].res_last_minute == 1"><div class="danger-icon"></div>
                      <span class="tooltiptext">{{courtResStrings.last_minute}}</span>
                    </div>
                    <div class="res-tooltip" style="margin-left: 10px;"><div class="info-icon"></div>
                      <span class="tooltiptext">{{whoReservedIt(n+6, res.reserved)[0].snippet}}</span>
                    </div>
                    <div class="res-tooltip" style="margin-left: 10px;" v-if="res.darktime <= n+6"><div class="lightbulb-icon"></div>
                      <span class="tooltiptext">{{courtResStrings.darktime}}</span>
                    </div>
                    <div class="res-tooltip" style="margin-left: 10px;" v-if="whoReservedIt(n+6, res.reserved)[0].res_berlet"><div class="ticket-icon"></div>
                      <span class="tooltiptext">{{courtResStrings.ticket}}</span>
                    </div>
                    <div class="res-tooltip" style="margin-left: 10px;" v-if="!whoReservedIt(n+6, res.reserved)[0].res_berlet"><div class="money-icon"></div>
                      <span class="tooltiptext">{{courtResStrings.money}}</span>
                    </div>
                    <div class="res-tooltip" style="margin-left: 10px; margin-right: auto;"><div class="edit-icon" @click="openEditReservationModal(whoReservedIt(n+6, res.reserved)[0].id)"></div>
                      <span class="tooltiptext">{{courtResStrings.editRes}}</span>
                    </div>
                    <a href="javascript:;" v-if="lessThan24H(n+6) === 0 || userdata.is_admin == 1" class="delete-this" @click="deleteThisReservation(whoReservedIt(n+6, res.reserved)[0].id)">{{courtResStrings.cancel_res}}</a>
                  </div>
                </div>
              </div>
              <div class="nowho" v-else>
                {{courtResStrings.reserved}}
                <div v-if="whoReservedIt(n+6, res.reserved).length > 0 && (whoReservedIt(n+6, res.reserved)[0].display_name == userdata.name || whoReservedIt(n+6, res.reserved)[0].display_name == userdata.full_name)">
                  <div class="res-btns">
                    <div class="res-tooltip"><div class="info-icon"></div>
                      <span class="tooltiptext">{{whoReservedIt(n+6, res.reserved)[0].snippet}}</span>
                    </div>
                    <div class="res-tooltip" style="margin-left: 10px;" v-if="res.darktime <= n+6"><div class="lightbulb-icon"></div>
                      <span class="tooltiptext">{{courtResStrings.darktime}}</span>
                    </div>
                    <div class="res-tooltip" style="margin-left: 10px;" v-if="whoReservedIt(n+6, res.reserved)[0].res_berlet"><div class="ticket-icon"></div>
                      <span class="tooltiptext">{{courtResStrings.ticket}}</span>
                    </div>
                    <div class="res-tooltip" style="margin-left: 10px;" v-if="!whoReservedIt(n+6, res.reserved)[0].res_berlet"><div class="money-icon"></div>
                      <span class="tooltiptext">{{courtResStrings.money}}</span>
                    </div>
                    <div class="res-tooltip" style="margin-left: 10px; margin-right: auto;"><div class="edit-icon" @click="openEditReservationModal(whoReservedIt(n+6, res.reserved)[0].id)"></div>
                      <span class="tooltiptext">{{courtResStrings.editRes}}</span>
                    </div>
                    <a href="javascript:;" v-if="lessThan24H(n+6) === 0 || userdata.is_admin == 1" class="delete-this" @click="deleteThisReservation(whoReservedIt(n+6, res.reserved)[0].id)">{{courtResStrings.cancel_res}}</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="reservation-modal" v-if="selectedTimesolts != 0">
      {{courtResStrings.selected}}: {{selectedTimesolts.length}}
      <a href="javascript:;" class="reserve-them-all" @click="openReserveModal()">{{courtResStrings.reserve}}</a>
    </div>

    <div class="send-reservation-modal">
      {{courtResStrings.selected}}:
      <div class="time" v-for="time in selectedTimesolts" :key="time.id">
        {{time.name}}: {{courtResStrings.today}} {{time.time}} {{courtResStrings.oclock}}
      </div>
      <div class="berletstatus">
        <p class="van" v-if="this.userdata.berlet - selectedTimesolts.length > 0">{{courtResStrings.vanBerlet}} {{this.userdata.berlet - selectedTimesolts.length}}</p>
        <p class="van" v-if="this.userdata.berlet - selectedTimesolts.length == 0">{{courtResStrings.elfogyottABerlet}}</p>
        <p class="van" v-if="this.userdata.berlet - selectedTimesolts.length < 0">{{courtResStrings.nincsBerlet.replace('%s', (this.userdata.berlet - selectedTimesolts.length) * -1)}}</p>
      </div>
      <div class="reservation-form">
        <div class="form-input">
          <p>{{courtResStrings.username}}</p>
          <input v-model="form_username" placeholder="John Doe">
        </div>
        <div class="form-input">
          <p>{{courtResStrings.email}}</p>
          <input v-model="form_email" placeholder="john.doe@email.com">
        </div>
        <div class="form-input">
          <p>{{courtResStrings.tel}}</p>
          <input v-model="form_tel" placeholder="+36 70 000 1111">
        </div>
        <div class="form-input">
          <p>{{courtResStrings.repeat}}</p>
          <input v-model="form_rep" type="number">
        </div>
        <div class="form-input">
          <p>{{courtResStrings.msg}}</p>
          <textarea v-model="form_msg" placeholder="Megjegyzését ide írhatja"></textarea>
        </div>
        <div class="form-row" v-if="userdata.is_admin == 1">
          <p>{{courtResStrings.user_select}}</p>
          <select v-model="selectedUser" class="reservation-users">
            <option v-for="user in alluser" :key="user.id" v-bind:value="user.id">
              {{ user.display_name }}
            </option>
          </select>
        </div>
      </div>
      <p>{{courtResStrings.really}}</p>
      <a href="javascript:;" class="reserve-them-all" @click="reserveThemAll">{{courtResStrings.reserve}}</a>
      <a href="javascript:;" class="cancel-btn" @click="cancelResModal">{{courtResStrings.cancel}}</a>
    </div>

    <div class="edit-reservation-modal">
      <div class="reservation-form">
        <div class="form-input">
          <p>{{courtResStrings.username}}</p>
          <input v-model="edit_form_username" placeholder="John Doe">
        </div>
        <div class="form-input">
          <p>{{courtResStrings.email}}</p>
          <input v-model="edit_form_email" placeholder="john.doe@email.com">
        </div>
        <div class="form-input">
          <p>{{courtResStrings.tel}}</p>
          <input v-model="edit_form_tel" placeholder="+36 70 000 1111">
        </div>
        <div class="form-input">
          <p>{{courtResStrings.msg}}</p>
          <textarea v-model="edit_form_msg" placeholder="Megjegyzését ide írhatja"></textarea>
        </div>
        <div class="form-row" v-if="userdata.is_admin == 1">
          <p>{{courtResStrings.user_select}}</p>
          <select v-model="edit_selectedUser" class="reservation-users">
            <option v-for="user in alluser" :key="user.id" v-bind:value="user.id">
              {{ user.display_name }}
            </option>
          </select>
        </div>
      </div>
      <a href="javascript:;" class="reserve-them-all" @click="editThemAll">{{courtResStrings.editRes}}</a>
      <a href="javascript:;" class="cancel-btn" @click="cancelEditResModal">{{courtResStrings.cancel}}</a>
    </div>

    <div class="res-successful-popup">
      <div style="margin-bottom: 30px;">
        {{courtResStrings.succesful_res}}
      </div>
      <a href="javascript:;" class="cancel-btn" @click="closeSucRepModal" style="float: right;">{{courtResStrings.close}}</a>
    </div>
    <div class="res-unsuccessful-popup">
      <div class="emptycontent" style="margin-bottom: 30px;">
      </div>
      <a href="javascript:;" class="cancel-btn" @click="closeUnSucRepModal" style="float: right;">{{courtResStrings.close}}</a>
    </div>
    
    <div class="update-response-popup">
      <div class="emptycontent" style="margin-bottom: 30px;">
      </div>
      <a href="javascript:;" class="cancel-btn" @click="closeUpdateResponsePopup" style="float: right;">{{courtResStrings.close}}</a>
    </div>
  </div>
</template>

<script>
import axios from "axios";
export default {
  name: "Reservation",
  data() {
    return {
      monthhelper: ['01','02','03','04','05','06','07','08','09','10','11','12'],
      courtResStrings: '',
      results: [],
      editResults: [],
      userdata: '',
      editID: '',
      alluser: [],
      wpapi: '',
      curDate: '',
      curDay: '',
      curHour: '',
      selectedTimesolts: [],
      selectedUser: '',
      form_username: '',
      form_email: '',
      form_tel: '',
      form_msg: '',
      edit_form_username: '',
      edit_form_email: '',
      edit_form_tel: '',
      edit_form_msg: '',
      edit_selectedUser: '',
      form_rep: '',
      selectedDate: '',
    };
  },
  mounted() {
    this.setUserData()
    this.getDate()
    this.reservationData()
    this.getAllUsers()
  },
  updated: function () {
    this.$nextTick(function () {
      this.upDate()
    })
  },

  methods: {
    reservationData () {
      let dateToGet = this.curDate.getFullYear() + '-' + ("0" + (this.curDate.getMonth() + 1)).slice(-2) + '-' + ("0" + this.curDate.getDate()).slice(-2)
      //console.log(dateToGet)
      axios
        .get(this.wpapi.resturl + "wpse/v1/rest_court_reservation", { params: { date: dateToGet } })
        .then(response => {
          this.results = response.data;
        })
        .catch(error => {
          console.log(error);
        });
    },
    getAllUsers(){
      axios
        .get(this.wpapi.resturl + "wpse/v1/all_users")
        .then(response => {
          this.alluser = response.data;
        })
        .catch(error => {
          console.log(error);
        });
    },
    setUserData(){
      //console.log('wpAPI: ', wpApiSettings)
      //console.log('userdata: ', userData)
      this.userdata = userData
      this.wpapi = wpApiSettings
      this.courtResStrings = courtResStrings
      this.form_username = userData.full_name
      this.form_tel = userData.phone
      this.form_email = userData.email
    },
    getDate(){
      this.curDate = new Date()
      this.curDay = this.curDate.getDate()
      this.curHour = this.curDate.getHours()
      this.selectedDate = this.curDate.getFullYear() + '-' + this.monthhelper[this.curDate.getMonth()] + '-' + ("0" + this.curDate.getDate()).slice(-2)
    },
    upDate(){
      this.curDay = this.curDate.getDate()
      this.curHour = this.curDate.getHours()
    },
    isCourtReservable(time, reservation){
      let ret = 0
      let help = new Date()
      help.setDate(help.getDate() - 1)
      if(time >= reservation.starttime && time < reservation.endtime){
        if(time > this.curHour){
          ret = 1
        }
        if(this.curDate.getMonth() > new Date().getMonth() || this.curDate.getYear() > new Date().getYear() || this.curDate.getDate() > new Date().getDate()){
          ret = 1
        }
        if(this.curDate < help){
          ret = 0
        }
      }
      return ret
    },
    isCourtAvailableThisTime(time, reservations){
      let csreturn = 1;
      Array.prototype.forEach.call(reservations, (cres)=>{
        let crestime = new Date(cres.time.replace(/-/g, "/"))
        if(crestime.getHours() == time && this.curDate.getMonth() == crestime.getMonth() && this.curDate.getYear() == crestime.getYear() && this.curDate.getDate() == crestime.getDate()){
          csreturn = 0;
        }
      })
      return csreturn;
    },
    whoReservedIt(time, reservations){
      let kurd = this.curDate
      return reservations.filter(function(res) {
        let tojm = new Date(res.time.replace(/-/g, "/"))
        return (tojm.getHours() == time && tojm.getYear() === kurd.getYear() && tojm.getMonth() === kurd.getMonth() && tojm.getDate() === kurd.getDate())
      })
    },
    updateSelectedTimeslots(id, court, timeslot){
      if(this.selectedTimesolts.some(item => item.name === court && item.time === timeslot)){
        this.selectedTimesolts = this.selectedTimesolts.filter(function(item) {
          return !(item.name === court && item.time === timeslot)
        })
      } else{
        this.selectedTimesolts.push({'id': id, 'name': court, 'time': timeslot})
      }
      //console.log(this.selectedTimesolts)
    },
    isClicked(court, timeslot){
      if(this.selectedTimesolts.some(item => item.name === court && item.time === timeslot)){
        return 'clicked'
      }
      return ''
    },
    lessThan24H(timey){
      let dateNow = new Date()
      dateNow.setDate(dateNow.getDate() + 1)
      let dateTomorrow = new Date(this.curDate)
      dateTomorrow.setHours(timey)
      if(dateNow < dateTomorrow){
        return 0
      } else {
        return 1
      }
    },
    dateNext(){
      this.curDate.setDate(this.curDate.getDate() + 1)
      this.selectedDate = this.curDate.getFullYear() + '-' + this.monthhelper[this.curDate.getMonth()] + '-' + ("0" + this.curDate.getDate()).slice(-2)
      this.selectedTimesolts = []
      this.reservationData()
    },
    dateBack(){
      this.curDate.setDate(this.curDate.getDate() - 1)
      this.selectedDate = this.curDate.getFullYear() + '-' + this.monthhelper[this.curDate.getMonth()] + '-' + ("0" + this.curDate.getDate()).slice(-2)
      this.selectedTimesolts = []
      this.reservationData()
    },
    selectThanSetDate(){
      this.curDate = new Date(this.selectedDate.replace(/-/g, "/"))
      this.curDate.setHours(this.curHour)
      this.selectedTimesolts = []
      this.reservationData()
    },
    clearSelections(){
      this.selectedTimesolts = []
      setTimeout(() => {
        this.reservationData()
      }, 500);
    },
    reserveThemAll(){
      let reqParams = []
      let moth
      let dey
      if(this.curDate.getMonth() < 9){
        moth = '0' + (this.curDate.getMonth()+1)
      }
      else{
        moth = this.curDate.getMonth()+1
      }
      if(this.curDay < 10){
        dey = '0' + this.curDay
      }
      else{
        dey = this.curDay
      }
      let reqDate = this.curDate.getFullYear() + '-' + moth + '-' + dey + ' ';
      let cntSel = this.selectedTimesolts.length
      this.selectedTimesolts.forEach((ts)=>{
        reqParams.push({
          'userid': this.userdata.id,
          'courtid': ts.id,
          'time': reqDate + ts.time + ':00:00',
          'selecteduser': this.selectedUser,
          'form_name': this.form_username,
          'form_email': this.form_email,
          'form_tel': this.form_tel,
          'form_msg': this.form_msg,
          'form_rep': this.form_rep,
        })
      })
      axios.defaults.headers.common['X-WP-Nonce'] = this.wpapi.nonce
      axios.post(this.wpapi.resturl + 'wpse/v1/add_rest_court_reservation', reqParams )
        .then((response) => {
          //console.log(response)
          if(response.data.status == 200){
            this.clearSelections()
            document.querySelector('.send-reservation-modal').classList.remove('show')
            document.querySelector('.res-successful-popup').classList.add('show')
            this.userdata.berlet -= cntSel
          }
          if(response.data.status == 400){
            this.clearSelections()
            document.querySelector('.send-reservation-modal').classList.remove('show')
            document.querySelector('.res-unsuccessful-popup .emptycontent').innerHTML = response.data.response
            document.querySelector('.res-unsuccessful-popup').classList.add('show')
            this.userdata.berlet -= cntSel + response.data.num
          }
        })
        .catch((error) => {
          console.log(error);
        });
      this.reservationData()
    },
    editThemAll(){
      let editReqParams = {
        'id': this.editID,
        'userid': this.userdata.id,
        'selecteduser': this.edit_selectedUser,
        'form_name': this.edit_form_username,
        'form_email': this.edit_form_email,
        'form_tel': this.edit_form_tel,
        'form_msg': this.edit_form_msg,
      }
      axios.defaults.headers.common['X-WP-Nonce'] = this.wpapi.nonce
      axios.post(this.wpapi.resturl + 'wpse/v1/edit_rest_court_reservation', editReqParams )
        .then((response) => {
          this.clearSelections()
          document.querySelector('.edit-reservation-modal').classList.remove('show')
          document.querySelector('.update-response-popup .emptycontent').innerHTML = response.data.response
          document.querySelector('.update-response-popup').classList.add('show')
        })
        .catch((error) => {
          console.log(error);
        });
      this.reservationData()
    },
    cancelResModal(){
      document.querySelector('.send-reservation-modal').classList.remove('show')
    },
    cancelEditResModal(){
      document.querySelector('.edit-reservation-modal').classList.remove('show')
    },
    closeSucRepModal(){
      document.querySelector('.res-successful-popup').classList.remove('show')
    },
    closeUnSucRepModal(){
      document.querySelector('.res-unsuccessful-popup').classList.remove('show')
    },
    closeUpdateResponsePopup(){
      document.querySelector('.update-response-popup').classList.remove('show')
    },
    deleteThisReservation(id){
      //console.log(id)
      axios.defaults.headers.common['X-WP-Nonce'] = this.wpapi.nonce
      axios.post(this.wpapi.resturl + 'wpse/v1/delete_rest_court_reservation', {'id': id} )
        .then((response) => {
          if(response.status == 200)
            console.log("Sikeres törlés")
        })
        .catch((error) => {
          console.log(error);
        });
      this.reservationData()
    },
    openReserveModal(){
      document.querySelector(".send-reservation-modal").classList.add("show")
    },
    openEditReservationModal(id){
      this.editID = id
      axios.get(this.wpapi.resturl + "wpse/v1/get_reservation_data", {params: { id: id }})
        .then(response => {
          this.editResults = response.data[0];
          this.edit_form_username = response.data[0].res_name
          this.edit_form_email = response.data[0].res_email
          this.edit_form_tel = response.data[0].res_tel
          this.edit_form_msg = response.data[0].snippet
          this.edit_selectedUser = response.data[0].user_id
          console.log(response.data)
        })
        .catch(error => {
          console.log(error);
        });
      document.querySelector(".edit-reservation-modal").classList.add("show")
    }
  },
};
</script>

<style>
.reservation-container{
  width: 100%;
  margin: auto;
  display: flex;
  flex-direction: column;
  padding: 0 15px;
  box-sizing: border-box;
  max-width: 1230px;
  font-family: 'Roboto Condensed';
  color: #ce6228;
}
.reservation-container *{
  box-sizing: border-box;
}
.reservation-calendar{
  border-radius: 5px;
}
.reservation-calendar .timeslots{
  padding: 0 10px;
}
.reservation-calendar .timeslots .time .reservable, .reservation-calendar .timeslots .time .non-reservable{
  margin: 5px 0;
  background-color: rgb(73 255 12 / 49%);
  box-shadow: 0 0 6px -4px black;
  padding: 10px;
}
.reservation-calendar .timeslots .time .reservable{
  cursor: pointer;
}
.reservation-calendar .timeslots .time .non-reservable{
  background-color: #eb1b1bb6;
  color: white;
}
.reservation-calendar .timeslots .time .reservable:hover, .reservation-calendar .timeslots .time .reservable.clicked{
  background-color: #edd79f;
}
.reservation-calendar .timeslots.fedett .time .reservable:hover, .reservation-calendar .timeslots.fedett .time .reservable.clicked{
  background-color: #99cfff;
}
.reservation-calendar .timeslots .time .non-reservable.mine{
  background-color: #edd79f;
  display: block;
  color: #ce6228;
}
.reservation-calendar .courtname{
  background: rgb(255,255,255);
  background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(218,165,32,0.6839110644257703) 100%);
  text-align: center;
  padding: 10px;
  margin: 0 5px;
  font-weight: 600;
}
.reservation-calendar .datepicker .date{
  font-size: 20px;
}
.res-col-wrap{
  display: -ms-flexbox;
  display: flex;
  -ms-flex-wrap: wrap;
  flex-wrap: wrap;
  box-sizing: border-box;
  justify-content: center;
}
.res-col-wrap .res-column{
  width: 25%;
  box-sizing: border-box;
  min-width: 250px;
  margin-top: 20px;
}
.non-reservable .delete-this{
  font-size: 16px;
  color: red;
  font-weight: 600;
  float: right;
}
.reservation-modal{
  position: fixed;
  bottom: 10px;
  left: 50%;
  transform: translateX(-50%);
  padding: 20px;
  background-color: #fcf7ea;
  border-radius: 10px;
  border: 1px solid black;
  z-index: 999;
  width: fit-content;
}
.send-reservation-modal, .edit-reservation-modal{
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  padding: 40px 50px;
  background-color: #fcf7ea;
  border-radius: 10px;
  border: 1px solid #000;
  display: flex;
  flex-direction: column;
  visibility: hidden;
  z-index: 999;
  width: 1000px;
  max-height: 96%;
  overflow: scroll;
}
.send-reservation-modal.show, .edit-reservation-modal.show{
  visibility: visible;
}
.reservation-users{
  height: 42px;
  border-radius: 5px;
  margin: 10px 0 30px 0;
  width: 100%;
}
.reservation-form{
  margin: 20px 0;
  padding: 10px 0;
  border-top: 1px solid;
  border-bottom: 1px solid;
}
.reservation-form input{
  height: 42px;
  width: 100%;
}
.reservation-form textarea{
  height: 84px;
  width: 100%;
}
.reservation-form p{
  margin: 10px 0 0 0;
}

.res-btns{
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}
.res-tooltip {
  position: relative;
  display: inline-block;
  font-size: 22px;
  cursor: pointer;
}
.res-tooltip .tooltiptext {
  visibility: hidden;
  width: fit-content;
  background-color: #555;
  color: #fff;
  text-align: center;
  padding: 5px 20px;
  border-radius: 6px;
  position: absolute;
  z-index: 1;
  bottom: 125%;
  left: 50%;
  transform: translateX(-50%);
  font-size: 16px;
}
.res-tooltip .tooltiptext::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  margin-left: -5px;
  border-width: 5px;
  border-style: solid;
  border-color: #555 transparent transparent transparent;
}
.res-tooltip:hover .tooltiptext {
  visibility: visible;
  opacity: 1;
}
.reservation-container .days{
  width: 100%;
  display: flex;
  flex-direction: column;
}
.reservation-container .days .nav{
  width: 100%;
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}
.reservation-container .days .prev, .reservation-container .days .next{
  cursor: pointer;
  font-weight: 600;
}
.reservation-container .days .prev:hover, .reservation-container .days .next:hover{
  text-decoration: underline;
}
.reservation-container .days .day{
  display: flex;
  justify-content: center;
  margin-bottom: 20px;
}
.reservation-container .days .is_admin{
  text-align: center;
  background-color: rgb(255 0 0 / 71%);
  padding: 10px;
  color: white;
}
.reservation-modal .reserve-them-all, .send-reservation-modal .reserve-them-all, .edit-reservation-modal .reserve-them-all{
  font-family: inherit;
  font-size: inherit;
  color: #e86c51;
  background-color: transparent;
  border: 2px solid #e86c51;
  padding: 10px 25px;
  border-radius: 10px;
  transition: all .2s ease;
  text-decoration: none;
  width: fit-content;
}
.reservation-modal .reserve-them-all{
  margin-left: 100px;
}
.reservation-modal .reserve-them-all:hover, .send-reservation-modal .reserve-them-all:hover, .edit-reservation-modal .reserve-them-all:hover{
  background-color: #e86c51;
  color: white;
}
.send-reservation-modal .cancel-btn, .edit-reservation-modal .cancel-btn{
  margin-top: 20px;
  text-align: right;
  color: #0059a0;
  text-decoration: underline;
}
.res-successful-popup, .res-unsuccessful-popup, .update-response-popup{
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  padding: 40px 50px;
  background-color: #fcf7ea;
  border-radius: 10px;
  border: 1px solid #000;
  display: flex;
  flex-direction: column;
  visibility: hidden;
  display: none;
  z-index: 999;
  width: 1000px;
}
.res-successful-popup.show, .res-unsuccessful-popup.show, .update-response-popup.show{
  visibility: visible;
  display: block;
}
.lightbulb-icon{
  width: 18px;
  height: 18px;
  background-image: url('./assets/light.png');
  background-size: contain;
}
.danger-icon{
  width: 18px;
  height: 18px;
  background-image: url('./assets/danger.png');
  background-size: contain;
}
.info-icon{
  width: 18px;
  height: 18px;
  background-image: url('./assets/info.png');
  background-size: contain;
}
.edit-icon{
  width: 18px;
  height: 18px;
  background-image: url('./assets/edit.png');
  background-size: contain;
}
.money-icon{
  width: 18px;
  height: 18px;
  background-image: url('./assets/money.png');
  background-size: contain;
}
.ticket-icon{
  width: 18px;
  height: 18px;
  background-image: url('./assets/ticket.png');
  background-size: contain;
}
@media screen and (max-width: 1200px) {
  .send-reservation-modal, .edit-reservation-modal{
    width: 96%;
    padding: 20px;
  }
  .res-successful-popup, .res-unsuccessful-popup, .update-response-popup{
    width: 96%;
  }
}
@media screen and (max-width: 768px) {
  .reservation-modal{
    width: 96%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }
  .reservation-modal .reserve-them-all{
    margin-left: 0;
    margin-top: 20px;
  }
  .res-col-wrap .res-column{
    width: 100%;
    height: 50vh;
    overflow: hidden;
    overflow-y: scroll;
  }
  .reservation-calendar .courtname{
    border-top: 10px solid;
    position: sticky;
    top: 0;
    background: #e5cd84;
    border-bottom: 2px solid;
    z-index: 1;
  }
}
</style>