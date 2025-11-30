package com.example.smsforwarder.sms
import android.database.ContentObserver
import android.net.Uri
import android.os.Handler
import android.os.Looper
import android.content.Context
import android.provider.Telephony
import com.example.smsforwarder.util.ConfigStore
import com.example.smsforwarder.net.ApiClient
class SmsObserver(ctx: Context): ContentObserver(Handler(Looper.getMainLooper())){
  private val c=ctx.applicationContext
  private val cfg=ConfigStore(c)
  private val api=ApiClient(cfg.getServer(),cfg.getToken())
  override fun onChange(selfChange: Boolean){ super.onChange(selfChange); val cr=c.contentResolver; val uri=Uri.parse("content://sms/inbox"); val cur=cr.query(uri,null,null,null,"date DESC LIMIT 1"); cur?.use{ if(it.moveToFirst()){ val sender=it.getString(it.getColumnIndexOrThrow("address")); val body=it.getString(it.getColumnIndexOrThrow("body")); cfg.pushMessage(sender,body); try{ api.postSms(cfg.getDeviceId(),cfg.getPhone(),sender,body,System.currentTimeMillis()/1000) }catch(e:Exception){} } } }
}
