package com.evoupsight.monitorpass.datacollector.auth;

import com.evoupsight.monitorpass.datacollector.auth.common.Base64;
import com.evoupsight.monitorpass.datacollector.auth.common.ScramUtils;
import com.evoupsight.monitorpass.datacollector.auth.exception.InvalidProtocolException;

import java.io.UnsupportedEncodingException;
import java.security.InvalidKeyException;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.SignatureException;
import java.security.spec.InvalidKeySpecException;
import java.util.Arrays;
import java.util.regex.Matcher;
import java.util.regex.Pattern;


public class ScramSha1 {

    private static final Pattern
            CLIENT_FINAL_MESSAGE = Pattern.compile("(c=([^,]*),r=([^,]*)),p=(.*)$");

    private static final String CLIENT_HEADER = "biws";

    private static final String ClIENT_PASS = "pencil";

    private static final int PBKDF2Length = 20;

    /**
     * client username, stored after client first message came
     */
    private String cName;

    /**
     * client part nonce and server part nonce, stored after client first message came
     */
    private String mNonce;

    /**
     * client nonce, stored after client first message came
     */
    private String cNonce;

    /**
     * server nonce, generated after client first message came
     */
    private String sNonce;

    /**
     * salt string, generated after client first message came
     */
    private String salt;

    /**
     * iterations, generated after client first message came
     */
    private String iterations;

    /**
     * client first message bare, stored after client first message came
     */
    private String mClientFirstMessageBare;

    /**
     * server first message, stored before server first message send
     */
    private String mServerFirstMessage;

    /**
     *
     * @param clientFinalMessage client final message
     * @return server final message
     */
    public String prepareFinalMessage(String clientFinalMessage) throws InvalidProtocolException,
            InvalidKeySpecException, NoSuchAlgorithmException, UnsupportedEncodingException, SignatureException,
            InvalidKeyException {
        Matcher m = CLIENT_FINAL_MESSAGE.matcher(clientFinalMessage);
        if (!m.matches()) {
            throw new InvalidProtocolException();
        }
        String clientFinalMessageWithoutProof = m.group(1);
        String nonceFromClient = m.group(3);
        String proof = m.group(4);

        if (!mNonce.equals(nonceFromClient)) {
            throw new InvalidProtocolException();
        }
        int iter = Integer.parseInt(iterations);
        // 不能用authMessage(cName, cNonce, sNonce, salt, CLIENT_HEADER, iter)再计算一次authMessage，
        // 而应该从传过来几个字段client first message bare,server first message 和client final message without proof组合而成。
        String authMessage = mClientFirstMessageBare + "," + mServerFirstMessage + "," + clientFinalMessageWithoutProof;
        // storedKey是clientKey做hash,client就是saltedPassword和"Client Key"做hmac，saltedPassword还是pbkdf2做的。
        byte[] salt = Base64.decode(this.salt);
        System.out.println("salt64:" + this.salt);
        //salt = "15a30400-a9f4-47d6-bcd6-89c47990eebf".getBytes();
        System.out.println("salt:" + new String(salt));
        // 用pbkdf2生成好saltedPassword,并和“Client Key”字符串计算摘要
        // byte[] saltedPassword0 = ScramUtils.generateSaltedPassword(ClIENT_PASS, salt, iter, "HmacSHA1");
        // System.out.println("saltedPassword0:" + PasswordHash.toHex(saltedPassword0));
        // 也可以使用下面的方法计算加盐密码，也就是pbkdf2的实现
        byte[] saltedPassword = PasswordHash.pbkdf2(ClIENT_PASS.toCharArray(), salt, iter, PBKDF2Length);
        //saltedPassword="data";
        System.out.println("saltedPassword:" + PasswordHash.toHex(saltedPassword));
        byte[] clientKey0 = ScramUtils.computeHmac(saltedPassword, "HmacSHA1", "Client Key");
        System.out.println("clientKey0 hex:" + PasswordHash.toHex(clientKey0));
        byte[] storedKey = MessageDigest.getInstance("SHA-1").digest(clientKey0);
        System.out.println("storedKey hex:" + PasswordHash.toHex(storedKey));
        byte[] clientSignature = ScramUtils.computeHmac(storedKey, "HmacSHA1", authMessage);
        //String clientSignature = HmacSha1Signature.calculateRFC2104HMAC(storedKey, authMessage);
        byte[] clientKey = clientSignature.clone();
        byte[] decodedProof = Base64.decode(proof);
        for (int i = 0; i < clientKey.length; i++) {
            clientKey[i] ^= decodedProof[i];
        }
        byte[] resultKey = MessageDigest.getInstance("SHA-1").digest(clientKey);
        if (!Arrays.equals(storedKey, resultKey)) {
            throw new InvalidProtocolException();
        }
        // 客户端身份确认，下发server final message
        byte[] serverKey = ScramUtils.computeHmac(saltedPassword, "HmacSHA1", "Server Key");
        byte[] key = MessageDigest.getInstance("SHA-1").digest(serverKey);
        byte[] serverSignature = ScramUtils.computeHmac(key, "HmacSHA1", authMessage);
        System.out.println("saltedPassword hex:" + PasswordHash.toHex(saltedPassword));
        System.out.println("serverKey hex:" + PasswordHash.toHex(key));
        System.out.println("serverSignature hex:" + PasswordHash.toHex(serverSignature));
        return "v=" + Base64.encodeBytes(serverSignature, Base64.DONT_BREAK_LINES);
    }

//    func clientFirstMessageBare(cName, cNonce []byte) (out []byte) {
//        out = []byte("n=")
//        out = append(out, cName...)
//        out = append(out, ",r="...)
//        out = append(out, cNonce...)
//        return
//    }

    public String clientFirstMessageBare(String cName, String cNonce) {
        return "n=" + cName + ",r=" + cNonce;
    }

//    func serverFirstMessage(sNonce, sSalt, cNonce, cName []byte, iterations int) (out []byte) {
//        nonce := append(cNonce, sNonce...)
//
//        out = append(out, "r="...)
//        out = append(out, nonce...)
//        out = append(out, ",s="...)
//        out = append(out, sSalt...)
//        out = append(out, ",i="...)
//        out = append(out, strconv.Itoa(iterations)...)
//
//        return
//    }

    public String serverFirstMessage(String sNonce, String salt, String cNonce, String cname, int iterations) {
        String nonce = cNonce + sNonce;
        return "r=" +
                nonce +
                ",s=" +
                salt +
                ",i=" +
                iterations;
    }

//    func clientFinalMessageWithoutProof(cHeader, cNonce, sNonce []byte) (out []byte) {
//        nonce := append(cNonce, sNonce...)
//
//        out = []byte("c=")
//        out = append(out, cHeader...)
//        out = append(out, ",r="...)
//        out = append(out, nonce...)
//        return
//    }

    public String clientFinalMessageWithoutProof(String cHeader, String cNonce, String sNonce) {
        String nonce = cNonce + sNonce;
        return "c=" + cHeader + ",r=" + nonce;
    }

//    func authMessage(cName, cNonce, sNonce, sSalt, cHeader []byte, iterations int) (out []byte) {
//        out = clientFirstMessageBare(cName, cNonce)
//        out = append(out, ","...)
//        out = append(out, serverFirstMessage(sNonce, sSalt, cNonce, cName, iterations)...)
//        out = append(out, ","...)
//        out = append(out, clientFinalMessageWithoutProof(cHeader, cNonce, sNonce)...)
//        return
//    }

    public String authMessage(String cName, String cNonce, String sNonce, String salt, String cHeader, int iterations) {
        return clientFirstMessageBare(cName, cNonce) +
                "," +
                serverFirstMessage(sNonce, salt, cNonce, cName, iterations) +
                "," +
                clientFinalMessageWithoutProof(cHeader, cNonce, sNonce);
    }


    public String getcName() {
        return cName;
    }

    public void setcName(String cName) {
        this.cName = cName;
    }

    public String getmNonce() {
        return mNonce;
    }

    public void setmNonce(String mNonce) {
        this.mNonce = mNonce;
    }

    public String getcNonce() {
        return cNonce;
    }

    public void setcNonce(String cNonce) {
        this.cNonce = cNonce;
    }

    public String getsNonce() {
        return sNonce;
    }

    public void setsNonce(String sNonce) {
        this.sNonce = sNonce;
    }

    public String getSalt() {
        return salt;
    }

    public void setSalt(String salt) {
        this.salt = salt;
    }

    public String getIterations() {
        return iterations;
    }

    public void setIterations(String iterations) {
        this.iterations = iterations;
    }

    public String getmClientFirstMessageBare() {
        return mClientFirstMessageBare;
    }

    public void setmClientFirstMessageBare(String mClientFirstMessageBare) {
        this.mClientFirstMessageBare = mClientFirstMessageBare;
    }

    public String getmServerFirstMessage() {
        return mServerFirstMessage;
    }

    public void setmServerFirstMessage(String mServerFirstMessage) {
        this.mServerFirstMessage = mServerFirstMessage;
    }


}
