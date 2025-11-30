package com.example.smsforwarder.net
import okhttp3.*
import com.google.gson.Gson
import java.util.concurrent.TimeUnit
class ApiClient(private val base:String, private val token:String){
  private val client=OkHttpClient.Builder().connectTimeout(10,TimeUnit.SECONDS).readTimeout(10,TimeUnit.SECONDS).build()
  private val gson=Gson()
  fun registerDevice(deviceId:String,phone:String):String?{
    val url=base.trimEnd('/')+"/api/auth/register_device.php"
    val body=gson.toJson(mapOf("device_id" to deviceId,"phone_number" to phone))
    val req=Request.Builder().url(url).post(RequestBody.create(MediaType.parse("application/json"),body)).build()
    client.newCall(req).execute().use{
      if(!it.isSuccessful) return null
      val s=it.body?.string()?:return null
      val m=gson.fromJson(s,Map::class.java)
      val t=m["token"]
      return if(t is String) t else null
    }
  }
  fun postHeartbeat(deviceId:String,phone:String,timestamp:Long):Boolean{
    val url=base.trimEnd('/')+"/api/heartbeat.php"
    val body=gson.toJson(mapOf("device_id" to deviceId,"phone_number" to phone,"timestamp" to timestamp))
    val req=Request.Builder().url(url).post(RequestBody.create(MediaType.parse("application/json"),body)).header("Authorization","Bearer "+token).build()
    client.newCall(req).execute().use{ return it.isSuccessful }
  }
  fun postSms(deviceId:String,phone:String,sender:String,content:String,ts:Long):Boolean{
    val url=base.trimEnd('/')+"/api/sms.php"
    val body=gson.toJson(mapOf("device_id" to deviceId,"phone_number" to phone,"sender" to sender,"content" to content,"receive_time" to ts))
    val req=Request.Builder().url(url).post(RequestBody.create(MediaType.parse("application/json"),body)).header("Authorization","Bearer "+token).build()
    client.newCall(req).execute().use{ return it.isSuccessful }
  }
}
