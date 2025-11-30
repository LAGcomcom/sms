package com.example.smsforwarder.service
import android.app.Service
import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.Notification
import android.content.Intent
import android.os.IBinder
import kotlinx.coroutines.*
import com.example.smsforwarder.util.ConfigStore
import com.example.smsforwarder.net.ApiClient
class HeartbeatService: Service(){
  private var job: Job?=null
  override fun onBind(intent: Intent?): IBinder?=null
  override fun onCreate(){
    val ch="heartbeat"
    val nm=getSystemService(NotificationManager::class.java)
    nm.createNotificationChannel(NotificationChannel(ch,"Heartbeat",NotificationManager.IMPORTANCE_LOW))
    val n=Notification.Builder(this,ch).setContentTitle("Heartbeat").setSmallIcon(android.R.drawable.stat_notify_sync).build()
    startForeground(1,n)
    val cfg=ConfigStore(this)
    val api=ApiClient(cfg.getServer(),cfg.getToken())
    val dev=cfg.getDeviceId()
    val phone=cfg.getPhone()
    job=CoroutineScope(Dispatchers.IO).launch{
      while(isActive){
        var ok=false
        var retry=0
        while(!ok && retry<3){
          try{ ok=api.postHeartbeat(dev,phone,System.currentTimeMillis()/1000) }catch(e:Exception){ ok=false }
          if(!ok){ retry++; delay(2000L*retry) }
        }
        val intent=Intent("com.example.smsforwarder.STATUS"); intent.putExtra("ok",ok); sendBroadcast(intent)
        delay(10000)
      }
    }
  }
  override fun onDestroy(){ job?.cancel() }
}
