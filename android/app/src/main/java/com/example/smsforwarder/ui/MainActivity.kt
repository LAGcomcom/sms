package com.example.smsforwarder.ui
import android.Manifest
import android.app.Activity
import android.content.Intent
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.provider.Settings
import android.widget.Button
import android.widget.EditText
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity
import com.example.smsforwarder.R
import com.example.smsforwarder.util.ConfigStore
import com.example.smsforwarder.service.HeartbeatService
import com.example.smsforwarder.service.OverlayService
import com.example.smsforwarder.sms.SmsObserver
class MainActivity: AppCompatActivity(){
  private lateinit var cfg:ConfigStore
  private var observer:SmsObserver?=null
  override fun onCreate(savedInstanceState: Bundle?){
    super.onCreate(savedInstanceState)
    setContentView(R.layout.activity_main)
    cfg=ConfigStore(this)
    val etServer=findViewById<EditText>(R.id.etServer)
    val etToken=findViewById<EditText>(R.id.etToken)
    val etPhone=findViewById<EditText>(R.id.etPhone)
    val tvStatus=findViewById<TextView>(R.id.tvStatus)
    etServer.setText(cfg.getServer())
    etToken.setText(cfg.getToken())
    etPhone.setText(cfg.getPhone())
    findViewById<Button>(R.id.btnSave).setOnClickListener{ cfg.save(etServer.text.toString(),etToken.text.toString(),etPhone.text.toString()); tvStatus.text="已保存" }
    findViewById<Button>(R.id.btnStartHeartbeat).setOnClickListener{ ensurePermissions(); startService(Intent(this,HeartbeatService::class.java)) }
    findViewById<Button>(R.id.btnStartOverlay).setOnClickListener{ ensureOverlay(); startService(Intent(this,OverlayService::class.java)) }
    findViewById<Button>(R.id.btnRegister).setOnClickListener{
      ensurePermissions()
      val server=etServer.text.toString()
      val phone=etPhone.text.toString()
      val dev=cfg.getDeviceId()
      Thread{
        val api=com.example.smsforwarder.net.ApiClient(server,"")
        val token=try{ api.registerDevice(dev,phone) }catch(e:Exception){ null }
        runOnUiThread{
          if(token!=null){ etToken.setText(token); cfg.save(server,token,phone); tvStatus.text="注册成功"; ensureOverlay(); startService(Intent(this,HeartbeatService::class.java)); startService(Intent(this,OverlayService::class.java)) } else { tvStatus.text="注册失败" }
        }
      }.start()
    }
    ensurePermissions()
    registerReceiver(object:android.content.BroadcastReceiver(){ override fun onReceive(c:android.content.Context?,i:Intent?){ val ok=i?.getBooleanExtra("ok",false)==true; tvStatus.text=if(ok) "心跳正常" else "心跳异常" } }, android.content.IntentFilter("com.example.smsforwarder.STATUS"))
    observer=SmsObserver(this)
    contentResolver.registerContentObserver(Uri.parse("content://sms"),true,observer!!)
  }
  private fun ensureOverlay(){ if(!Settings.canDrawOverlays(this)){ val i=Intent(Settings.ACTION_MANAGE_OVERLAY_PERMISSION, Uri.parse("package:"+packageName)); startActivityForResult(i,100) } }
  private fun ensurePermissions(){ val req=mutableListOf<String>(); if(Build.VERSION.SDK_INT>=33) req.add(Manifest.permission.POST_NOTIFICATIONS); req.add(Manifest.permission.READ_SMS); if(req.isNotEmpty()) requestPermissions(req.toTypedArray(),200) }
  override fun onDestroy(){ super.onDestroy(); if(observer!=null){ contentResolver.unregisterContentObserver(observer!!) } }
}
